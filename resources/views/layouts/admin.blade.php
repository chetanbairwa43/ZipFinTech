<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'zipFinTech') }}</title>
	
	<!-- plugins:css -->
	<link rel="stylesheet" href="{{ asset('admin/assets/vendors/feather/feather.css') }}">
	<link rel="stylesheet" href="{{ asset('admin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
	<link rel="stylesheet" href="{{ asset('admin/assets/vendors/ti-icons/css/themify-icons.css') }}">
	<link rel="stylesheet" href="{{ asset('admin/assets/vendors/css/vendor.bundle.base.css') }}">
	<link rel="stylesheet" href="{{ asset('admin/assets/css/vertical-layout-light/style.css') }}">
	<link rel="stylesheet" href="{{ asset('admin/assets/css/select2.min.css') }}">

	
	<!-- Custom Css Start -->
	<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
	<!-- Custom Css End -->
	<style>
		.text-uppercase{
			margin-left: -100px;
		}
		</style>
	<!-- <link rel="shortcut icon" href="images/favicon.png" /> -->
</head>

<body>
	
	<div class="container-scroller">
		@include('includes.admin.header')
		<div class="container-fluid page-body-wrapper">
			@include('includes.admin.sidebar')
			<div class="main-panel">
				
				@yield('content')

				<!-- <footer class="footer">
					<div class="d-sm-flex justify-content-center justify-content-sm-between">
					<span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Copyright © 2021. All rights reserved.</span>
					</div>
				</footer> -->
			</div>
			<div class="text-xs font-weight-bold text-success text-uppercase mb-1"> Dashboard </div>

			<!-- main-panel ends -->
		</div>
		<!-- page-body-wrapper ends -->
	</div>
	<!-- container-scroller -->


<!-- plugins:js -->
<script src="{{ asset('admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('admin/assets/vendors/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('admin/assets/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('admin/assets/js/template.js') }}"></script>
<!-- <script src="{{ asset('admin/assets/js/todolist.js') }}"></script> -->
<script src="{{ asset('admin/assets/js/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('admin/assets/js/select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('admin/assets/js/dashboard.js') }}"></script>

<!-- Custom js Start -->
<script src="{{ asset('js/custom.js') }}"></script>
<!-- Custom js End -->
@yield('js')
</body>

</html>