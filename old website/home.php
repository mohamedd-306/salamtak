<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salamtak - Your Safety App</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
}

/* ===== NAVBAR ===== */
nav.navbar{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    z-index:1000;
    background:rgba(0,0,0,0.3);
}

.navbar-brand{
    color:white !important;
    font-weight:700;
    font-size:22px;
}

.navbar .nav-link{
    color:white !important;
    font-weight:500;
    margin-right:20px;
}

nav .navbar-nav .nav-link:hover,
nav .navbar-brand:hover{
    transform:scale(1.08);
    color:white !important;
    transition:0.3s;
}

/* ===== HERO ===== */
.hero{
    height:100vh;
    background:
    linear-gradient(rgba(0,0,0,0.55),rgba(0,0,0,0.55)),
    url('image/home.jpeg') no-repeat center center;
    background-size:cover;
    display:flex;
    align-items:flex-end;
    color:white;
    padding:0 80px 80px;
}

.hero-content{
    max-width:600px;
}

.hero h1{
    font-size:80px;
    font-weight:bold;
}

.highlight{
    color:#f4c430;
    font-weight:600;
    font-size:26px;
    margin:20px 0;
}

.hero p{
    font-size:20px;
    margin-bottom:30px;
}

.store-btn{
    background:white;
    color:#1f3c88;
    padding:15px 25px;
    border-radius:40px;
    margin-right:15px;
    font-weight:600;
    display:inline-block;
    text-decoration:none !important;
    transition:0.3s;
}

.store-btn:hover{
    transform:scale(1.08);
}

/* ===== FOOTER ===== */
footer{
    background:#0f1d3f;
    color:white;
    padding:50px 0 20px;
}

.footer-title{
    font-size:28px;
    font-weight:bold;
    margin-bottom:15px;
}

.footer-text{
    color:#ccc;
    line-height:1.8;
}

/* LINKS BESIDE EACH OTHER */
.footer-links{
    display:flex;
    align-items:center;
    gap:15px;
    flex-wrap:wrap;
}

.footer-links a{
    display:inline-block;
    transition:0.3s;
}

.footer-links a:hover{
    transform:scale(1.15);
}

.footer-bottom{
    border-top:1px solid rgba(255,255,255,0.15);
    margin-top:30px;
    padding-top:15px;
    text-align:center;
    color:#bbb;
    font-size:14px;
}

/* ===== Responsive ===== */
@media (max-width:992px){

.hero{
    padding:0 30px 60px;
}

.hero h1{
    font-size:45px;
}

.highlight{
    font-size:18px;
}

.hero p{
    font-size:16px;
}

}

</style>
</head>

<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg navbar-dark">
<div class="container-fluid">

<a class="navbar-brand fw-bold" href="home.php">
<img src="image/logof.png" width="60" class="me-2">
Salamtak
</a>

<div class="collapse navbar-collapse justify-content-center">
<ul class="navbar-nav">
<li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
<li class="nav-item"><a class="nav-link" href="features.php">Features</a></li>
<li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
<li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
</ul>
</div>

</div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero">
<div class="hero-content">

<h1>Salamtak</h1>

<div class="highlight">
Your safety. Your route. Your voice.
</div>

<p>
Report, track, and stay informed about road problems and
public safety issues across Egypt. Join thousands making
our roads safer.
</p>

<a href="https://www.apple.com/eg/app-store/" class="store-btn" target="_blank">
Download on the App Store
</a>

<a href="https://play.google.com/" class="store-btn" target="_blank">
Get it on Google Play
</a>

</div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
<div class="container">
<div class="row">

<!-- Column 1 -->
<div class="col-md-4 mb-4">
<div class="footer-title">Salamtak</div>

<p class="footer-text">
Helping citizens report road issues, improve traffic safety,
and create smarter cities across Egypt.
</p>
</div>

<!-- Column 2 -->
<div class="col-md-4 mb-4">
<div class="footer-title">Quick Links</div>

<div class="footer-links">

<a href="https://www.apple.com/eg/app-store/" target="_blank">
<img src="image/alogo.png" width="20" height="20">
</a>

<a href="https://play.google.com/" target="_blank">
<img src="image/plogo.jpeg" width="20" height="20">
</a>

<a href="https://www.tiktok.com/" target="_blank">
<img src="image/tlogo.png" width="20" height="20">
</a>

<a href="https://www.instagram.com/" target="_blank">
<img src="image/instagram.png" width="20" height="20">
</a>

</div>
</div>

<!-- Column 3 -->
<div class="col-md-4 mb-4">
<div class="footer-title">Contact</div>

<p class="footer-text">
Cairo, Egypt <br>
support@salamtak.com <br>
+20 100 000 0000
</p>

</div>

</div>

<div class="footer-bottom">
© 2026 Salamtak. All Rights Reserved.
</div>

</div>
</footer>

</body>
</html>
