<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\ContactMessage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Security Test Suite — Aruvi on the Cliff
 *
 * Covers:
 *  - Authentication & access control
 *  - SQL injection prevention
 *  - XSS (cross-site scripting) prevention
 *  - CSRF protection
 *  - Rate limiting on login
 *  - File upload security
 *  - Privilege escalation prevention
 *  - Pending/suspended user blocking
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /* ── Helpers ──────────────────────────────────────────── */

    private function createSuperAdmin(): User
    {
        $role = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin', 'is_system' => true]);
        return User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);
    }

    private function createAdmin(): User
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'is_system' => false]);
        // Give admin all permissions
        $perm = Permission::create(['name' => 'View Messages', 'slug' => 'contact-messages.view']);
        $role->permissions()->attach($perm);

        return User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);
    }

    private function createPendingUser(): User
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'is_system' => false]);
        return User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'pending',
        ]);
    }

    private function createGuest(): User
    {
        $role = Role::create(['name' => 'Guest', 'slug' => 'guest', 'is_system' => true]);
        return User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);
    }

    /* ══════════════════════════════════════════════════════
       1. AUTHENTICATION TESTS
    ══════════════════════════════════════════════════════ */

    /** Unauthenticated users are redirected to login */
    public function test_unauthenticated_access_to_admin_is_redirected(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    /** Guests (non-admin roles) cannot access admin panel */
    public function test_guest_role_cannot_access_admin_dashboard(): void
    {
        $user = $this->createGuest();
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    /** Pending accounts are blocked at login */
    public function test_pending_user_cannot_login(): void
    {
        $user = $this->createPendingUser();

        $response = $this->post('/admin/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    /** Suspended accounts are blocked at login */
    public function test_suspended_user_cannot_login(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'is_system' => false]);
        $user = User::factory()->create(['role_id' => $role->id, 'status' => 'suspended']);

        $response = $this->post('/admin/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    /** Wrong credentials return error, not a 500 */
    public function test_wrong_credentials_return_validation_error(): void
    {
        $response = $this->post('/admin/login', [
            'email'    => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /** Super-admin can access dashboard */
    public function test_super_admin_can_access_dashboard(): void
    {
        $admin = $this->createSuperAdmin();
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    /* ══════════════════════════════════════════════════════
       2. CSRF PROTECTION
    ══════════════════════════════════════════════════════ */

    /** POST without CSRF token is rejected with 419 */
    public function test_post_without_csrf_token_is_rejected(): void
    {
        $response = $this->post('/admin/login', [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ], ['X-Requested-With' => 'XMLHttpRequest']); // no CSRF

        // 419 = Page Expired (CSRF mismatch)
        $response->assertStatus(419);
    }

    /** POST to contact form without CSRF is rejected */
    public function test_contact_form_requires_csrf(): void
    {
        $response = $this->post('/contact-us', [
            'name'    => 'Test',
            'email'   => 'test@test.com',
            'message' => 'Hello',
        ]);
        // Laravel will throw 419 or redirect; should not be 200 without CSRF
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    /* ══════════════════════════════════════════════════════
       3. SQL INJECTION TESTS
    ══════════════════════════════════════════════════════ */

    /** SQL injection in login email field does not crash or bypass auth */
    public function test_sql_injection_in_login_email_is_harmless(): void
    {
        $payloads = [
            "' OR '1'='1' --",
            "admin'--",
            "' OR 1=1 --",
            "'; DROP TABLE users; --",
            "\" OR \"\"=\"",
        ];

        foreach ($payloads as $payload) {
            $response = $this->post('/admin/login', [
                'email'    => $payload,
                'password' => 'anything',
            ]);

            // Should return validation error or redirect, never 200 with authenticated session
            $this->assertGuest("SQL injection payload should not authenticate: {$payload}");
        }
    }

    /** SQL injection in slug route parameter returns 404, not DB error */
    public function test_sql_injection_in_route_slug_returns_404(): void
    {
        $payloads = [
            "'; DROP TABLE rooms; --",
            "' OR '1'='1",
            '../etc/passwd',
        ];

        foreach ($payloads as $payload) {
            $response = $this->get('/rooms-suites/' . urlencode($payload));
            // Should be 404, not 500
            $this->assertContains(
                $response->getStatusCode(),
                [404, 301, 302],
                "Slug injection should not cause server error: {$payload}"
            );
        }
    }

    /** SQL injection in policy slug is restricted to whitelist */
    public function test_policy_slug_is_whitelisted(): void
    {
        $response = $this->get('/policies/' . urlencode("' OR 1=1 --"));
        $response->assertStatus(404);
    }

    /* ══════════════════════════════════════════════════════
       4. XSS PREVENTION TESTS
    ══════════════════════════════════════════════════════ */

    /** Review comment with XSS payload is escaped in output */
    public function test_review_xss_is_escaped_on_frontend(): void
    {
        $role = Role::create(['name' => 'Guest', 'slug' => 'guest', 'is_system' => true]);
        $xssPayload = '<script>alert("xss")</script>';

        \App\Models\Review::create([
            'name'        => 'XSS Tester',
            'rating'      => 5,
            'title'       => 'Test',
            'comment'     => $xssPayload,
            'is_approved' => true,
        ]);

        $response = $this->get('/');
        // The raw script tag should NOT appear in the response
        $response->assertDontSee('<script>alert("xss")</script>', false);
        // The escaped version should appear instead
        $response->assertSee('&lt;script&gt;', false);
    }

    /** Contact form name with XSS is stored and escaped, not executed */
    public function test_contact_form_xss_is_escaped(): void
    {
        $xssPayload = '<script>alert("xss")</script>';

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/contact-us', [
                'name'    => $xssPayload,
                'email'   => 'test@example.com',
                'message' => 'Test message',
            ]);

        $message = ContactMessage::latest()->first();
        $this->assertNotNull($message);

        // Stored value is the raw input (escaping happens at render time in Blade)
        $this->assertEquals($xssPayload, $message->name);

        // Confirm admin views it escaped
        $admin = $this->createSuperAdmin();
        // Give super admin permission
        $perm = Permission::create(['name' => 'View Messages', 'slug' => 'contact-messages.view']);
        $admin->role->permissions()->attach($perm);

        $response = $this->actingAs($admin)->get('/admin/messages');
        $response->assertDontSee('<script>alert("xss")</script>', false);
    }

    /* ══════════════════════════════════════════════════════
       5. PRIVILEGE ESCALATION TESTS
    ══════════════════════════════════════════════════════ */

    /** Regular admin cannot access super-admin approval panel */
    public function test_non_superadmin_cannot_access_approvals(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get('/admin/approvals');
        $response->assertStatus(403);
    }

    /** Regular admin cannot access role-permissions panel */
    public function test_non_superadmin_cannot_access_role_permissions(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get('/admin/role-permissions');
        $response->assertStatus(403);
    }

    /** Admin without permission cannot access restricted resource */
    public function test_admin_without_permission_cannot_view_resource(): void
    {
        $role = Role::create(['name' => 'Limited Admin', 'slug' => 'limited-admin', 'is_system' => false]);
        $user = User::factory()->create(['role_id' => $role->id, 'status' => 'active']);

        // No permissions assigned to this role
        $response = $this->actingAs($user)->get('/admin/hero-slides');
        $response->assertStatus(403);
    }

    /** Non-super-admin cannot delete another user */
    public function test_regular_admin_cannot_reject_pending_users(): void
    {
        $admin   = $this->createAdmin();
        $pending = $this->createPendingUser();

        $response = $this->actingAs($admin)
            ->delete("/admin/approvals/{$pending->id}/reject");

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $pending->id]);
    }

    /** Admin cannot approve users (super-admin only) */
    public function test_regular_admin_cannot_approve_pending_users(): void
    {
        $admin   = $this->createAdmin();
        $pending = $this->createPendingUser();

        $response = $this->actingAs($admin)
            ->patch("/admin/approvals/{$pending->id}/approve", ['role_id' => 1]);

        $response->assertStatus(403);
    }

    /* ══════════════════════════════════════════════════════
       6. FILE UPLOAD SECURITY TESTS
    ══════════════════════════════════════════════════════ */

    /** PHP file upload is rejected */
    public function test_php_file_upload_is_rejected(): void
    {
        $admin = $this->createSuperAdmin();
        Storage::fake('public');

        $file = UploadedFile::fake()->create('malicious.php', 100, 'application/x-php');

        $response = $this->actingAs($admin)
            ->post('/admin/theme-customization', [
                'site_logo' => $file,
            ]);

        $response->assertSessionHasErrors();
    }

    /** SVG file upload is rejected (XSS risk) */
    public function test_svg_file_upload_is_rejected(): void
    {
        $admin = $this->createSuperAdmin();
        Storage::fake('public');

        $file = UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml');

        $response = $this->actingAs($admin)
            ->post('/admin/theme-customization', [
                'site_logo' => $file,
            ]);

        $response->assertSessionHasErrors();
    }

    /** Valid image upload is accepted */
    public function test_valid_image_upload_is_accepted(): void
    {
        $admin = $this->createSuperAdmin();
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png', 100, 100);

        $response = $this->actingAs($admin)
            ->post('/admin/theme-customization', [
                'about_image' => $file,
            ]);

        // Should not fail with validation errors for a valid PNG
        $response->assertSessionMissing('errors');
    }

    /* ══════════════════════════════════════════════════════
       7. MASS ASSIGNMENT PROTECTION
    ══════════════════════════════════════════════════════ */

    /** Review submitted publicly cannot set is_approved=true */
    public function test_public_review_cannot_self_approve(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/reviews-feedback', [
                'name'        => 'Hacker',
                'rating'      => 5,
                'title'       => 'Great',
                'comment'     => 'Awesome place',
                'is_approved' => true,   // ← attempt to self-approve
            ]);

        $review = \App\Models\Review::where('name', 'Hacker')->first();
        $this->assertNotNull($review);
        // Must NOT be approved — controller forces false
        $this->assertFalse((bool) $review->is_approved);
    }

    /* ══════════════════════════════════════════════════════
       8. REGISTRATION SECURITY
    ══════════════════════════════════════════════════════ */

    /** New registration is set to pending, not active */
    public function test_new_registration_is_pending(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/admin/register', [
                'name'                  => 'New Admin',
                'email'                 => 'newadmin@test.com',
                'password'              => 'Password1',
                'password_confirmation' => 'Password1',
            ]);

        $user = User::where('email', 'newadmin@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pending', $user->status);
        $this->assertGuest('Pending user must not be auto-logged in');
    }

    /** Weak password is rejected */
    public function test_weak_password_is_rejected_on_registration(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/admin/register', [
            'name'                  => 'New Admin',
            'email'                 => 'newadmin@test.com',
            'password'              => '123456',     // too weak
            'password_confirmation' => '123456',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('users', ['email' => 'newadmin@test.com']);
    }

    /** Duplicate email is rejected on registration */
    public function test_duplicate_email_is_rejected(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $existing = $this->createSuperAdmin();

        $response = $this->post('/admin/register', [
            'name'                  => 'Duplicate',
            'email'                 => $existing->email,
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /* ══════════════════════════════════════════════════════
       9. DIRECT OBJECT ACCESS (IDOR)
    ══════════════════════════════════════════════════════ */

    /** Unauthenticated user cannot view admin messages */
    public function test_unauthenticated_cannot_view_admin_messages(): void
    {
        $response = $this->get('/admin/messages');
        $response->assertRedirect('/admin/login');
    }

    /** Admin cannot access non-existent resource (no server error) */
    public function test_invalid_resource_name_returns_404(): void
    {
        $admin = $this->createSuperAdmin();
        $response = $this->actingAs($admin)->get('/admin/nonexistent-resource-xyz');
        $response->assertStatus(404);
    }

    /* ══════════════════════════════════════════════════════
       10. HTML SANITIZER UNIT TEST
    ══════════════════════════════════════════════════════ */

    /** HtmlSanitizer strips script tags */
    public function test_html_sanitizer_strips_script_tags(): void
    {
        $dirty    = '<p>Hello</p><script>alert("xss")</script>';
        $clean    = \App\Support\HtmlSanitizer::clean($dirty);

        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringContainsString('<p>Hello</p>', $clean);
    }

    /** HtmlSanitizer strips onerror attributes */
    public function test_html_sanitizer_strips_event_handlers(): void
    {
        $dirty = '<p onerror="alert(1)">Text</p><img onload="hack()">';
        $clean = \App\Support\HtmlSanitizer::clean($dirty);

        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('onload', $clean);
    }

    /** HtmlSanitizer removes javascript: href */
    public function test_html_sanitizer_strips_javascript_href(): void
    {
        $dirty = '<a href="javascript:alert(1)">Click</a>';
        $clean = \App\Support\HtmlSanitizer::clean($dirty);

        $this->assertStringNotContainsString('javascript:', $clean);
    }

    /** HtmlSanitizer keeps safe tags */
    public function test_html_sanitizer_keeps_safe_tags(): void
    {
        $input = '<p>Hello <strong>World</strong></p><ul><li>Item</li></ul>';
        $clean = \App\Support\HtmlSanitizer::clean($input);

        $this->assertStringContainsString('<p>', $clean);
        $this->assertStringContainsString('<strong>', $clean);
        $this->assertStringContainsString('<ul>', $clean);
    }
}
