<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$title}}</title>
    <link rel="stylesheet" href="{{asset('template/default/assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('template/default/assets/css/dashboard.css')}}">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{asset('template/default/assets/fontawesome/css/fontawesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('template/default/assets/fontawesome/css/brands.min.css')}}">
    <link rel="stylesheet" href="{{asset('template/default/assets/fontawesome/css/solid.min.css')}}">
    <link rel="stylesheet" href="{{asset('template/default/assets/fontawesome/css/all.min.css')}}">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{asset('template/default/assets/sweetalert2/sweetalert2.min.css')}}">
    <script src="{{asset('template/default/assets/sweetalert2/sweetalert2.min.js')}}"></script>

    <!-- Flag Icon -->
    <link rel="stylesheet" href="{{asset('template/default/assets/flag-icon/css/flag-icons.min.css')}}">

    <!-- DataTable CSS -->
    <link rel="stylesheet" href="{{asset('template/default/assets/datatables/datatables.min.css')}}">

</head>

<body>

    <!-- Start Page Wrapper -->
    <div class="d-flex" id="wrapper">

        @yield('dashboard')

    </div>
    <!-- End Page Wrapper -->

    <script src="{{asset('template/default/assets/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('template/default/assets/js/jquery-3.6.1.min.js')}}"></script>
    <script src="{{asset('template/default/assets/jquery-validate/jquery.validate.min.js')}}"></script>
    <script src="{{asset('template/default/assets/jquery-validate/additional-methods.min.js')}}"></script>
    <script src="{{asset('template/default/assets/js/dashboard.js')}}"></script>
    <script src="{{asset('template/default/assets/datatables/datatables.min.js')}}"></script>

    @stack('user-home-js')

</body>

</html>