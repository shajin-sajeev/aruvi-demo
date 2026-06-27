@extends('layouts.frontend')
@section('title', 'Contact Us - Aruvi on the Cliff')
@section('content')
<section class="section section-soft"><div class="container"><div class="eyebrow">Contact</div><h1 class="display-5 fw-bold">Talk to our reservations desk</h1></div></section>
<section class="section"><div class="container"><form method="post" action="{{ route('contact.store') }}" class="lux-card p-4">@csrf<div class="row g-3"><div class="col-md-6"><input class="form-control" name="name" placeholder="Name" required></div><div class="col-md-6"><input class="form-control" name="email" type="email" placeholder="Email" required></div><div class="col-md-6"><input class="form-control" name="phone" placeholder="Phone"></div><div class="col-md-6"><input class="form-control" name="subject" placeholder="Subject"></div><div class="col-12"><textarea class="form-control" name="message" rows="5" placeholder="Message" required></textarea></div></div><button class="btn btn-teal mt-3">Send Message</button></form></div></section>
@endsection
