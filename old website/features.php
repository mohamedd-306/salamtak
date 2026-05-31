<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Salamtak - Features</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:#ffffff;
    padding-top:80px; /* navbar fixed */
}

/* ===== NAVBAR ===== */
nav.navbar {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 9999;
    background: #1e2a44 !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.navbar-brand{
    color: white !important;
    font-weight:700;
    font-size:22px;
}

.navbar .nav-link{
    color:white !important;
    margin-right:20px;
    font-weight:500;
}

.navbar .nav-link:hover{
    color:#f4c430 !important;
}

.download-btn{
    background:white;
    color:#1e2a44;
    padding:8px 18px;
    border-radius:40px;
    margin-left:10px;
    font-weight:500;
    text-decoration:none;
}

/* ===== FEATURES SECTION ===== */
.features-section{
    padding:80px 80px;
    text-align:center;
}

.badge-core{
    background:#f4c430;
    color:#1e2a44;
    padding:6px 20px;
    border-radius:20px;
    font-size:14px;
    font-weight:600;
    display:inline-block;
    margin-bottom:20px;
}

.features-title{
    font-size:48px;
    font-weight:700;
    color:#1e2a44;
}

.features-title span{
    color:#f4c430;
}

.features-text{
    color:#444;
    max-width:650px;
    margin:20px auto 50px auto;
    font-size:17px;
}

/* ===== CARDS ===== */
.feature-card{
    background:#f4f7fb;
    border-radius:25px;
    padding:40px 30px;
    text-align:left;
    transition:.3s;
    height:100%;
}

.feature-card:hover{
    transform:translateY(-8px);
    box-shadow:0px 12px 30px rgba(0,0,0,0.1);
}

.icon-box{
    width:55px;
    height:55px;
    background:#f4c430;
    border-radius:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    color:white;
    margin-bottom:20px;
}

/* Use Bootstrap Icons for uniform white icons */
.icon-box i {
    font-size: 24px;
    color: white;
}

.feature-card h5{
    font-weight:700;
    color:#1e2a44;
    margin-bottom:10px;
}

.feature-card p{
    color:#555;
    font-size:14px;
    line-height:1.6;
}

/* Responsive */
@media(max-width:992px){
    .features-section{
        padding:60px 30px;
    }
    .features-title{
        font-size:36px;
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

<!-- ===== FEATURES SECTION ===== -->
<section class="features-section">

<div class="badge-core">Core Features</div>

<h2 class="features-title">
Built for <span>Egyptian Roads</span>
</h2>

<p class="features-text">
Salamtak combines cutting-edge technology with community power 
to create the ultimate road safety platform for Egypt.
</p>

<div class="container">
<div class="row g-4">

<div class="col-lg-4 col-md-6">
<div class="feature-card">
<div class="icon-box"><i class="bi bi-shield-fill"></i></div>
<h5>Winch</h5>
<p>Request emergency towing service anytime.</p>
</div>
</div>

<div class="col-lg-4 col-md-6">
<div class="feature-card">
<div class="icon-box"><i class="bi bi-clock-fill"></i></div>
<h5>Potholes</h5>
<p>Report road potholes to help improve driving safety.</p>
</div>
</div>

<div class="col-lg-4 col-md-6">
<div class="feature-card">
<div class="icon-box"><i class="bi bi-map-fill"></i></div>
<h5>Light pole</h5>
<p>Report damaged or fallen street light poles.</p>
</div>
</div>

<div class="col-lg-4 col-md-6">
<div class="feature-card">
<div class="icon-box"><i class="bi bi-geo-alt-fill"></i></div>
<h5>Broken pipe</h5>
<p>Report water pipe leaks affecting the road.</p>
</div>
</div>

<div class="col-lg-4 col-md-6">
<div class="feature-card">
<div class="icon-box"><i class="bi bi-camera-fill"></i></div>
<h5>Report</h5>
<p>Send any type of hazard or road issue.</p>
</div>
</div>

<div class="col-lg-4 col-md-6">
<div class="feature-card">
<div class="icon-box"><i class="bi bi-people-fill"></i></div>
<h5>Broken glass</h5>
<p>Report glass or debris scattered on the road.</p>
</div>
</div>

</div>
</div>

</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>