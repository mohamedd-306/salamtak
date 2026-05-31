<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salamtak - Contact</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    font-family: 'Segoe UI', sans-serif;
    background:#f6f4ef;
    margin:0;
    padding:0;
    padding-top:80px; /* navbar fixed */
}

/* ===== NAVBAR ===== */
nav.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 9999;
    background: #1e2a44;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.navbar-brand {
    color: white !important;
    font-weight: 700;
    font-size: 22px;
}

.navbar .nav-link {
    color: white !important;
    margin-right: 20px;
    font-weight: 500;
}

.navbar .nav-link:hover {
    color: #d4af37 !important;
}

.download-btn {
    background: white;
    color: #1e2a44;
    padding: 8px 18px;
    border-radius: 40px;
    margin-left: 10px;
    text-decoration: none;
}

/* ===== HERO ===== */
.hero{
    text-align:center;
    padding:60px 20px 30px 20px;
}

.hero h1{
    font-weight:700;
    color:#1e2a44;
}

.hero p{
    color:#666;
    max-width:600px;
    margin:10px auto 0 auto;
}

/* ===== MAIN SECTION ===== */
.main-section{
    padding:40px 0 80px 0;
}

.card-custom{
    background:#ffffff;
    border-radius:20px;
    padding:35px;
    box-shadow:0 10px 30px rgba(0,0,0,0.05);
    height:100%;
}

/* FORM */
.form-control{
    border-radius:10px;
    padding:12px;
    margin-bottom:15px;
    background:#f5f5f5;
    border:none;
}

textarea{
    resize:none;
}

.btn-send{
    background:#1e2a44;
    color:white;
    border:2px solid #1e2a44;
    border-radius:25px;
    padding:8px 25px;
    font-size:14px;
    font-weight:500;
    transition:0.3s;
}

.btn-send:hover{
    background:white;
    color:#1e2a44;
}

/* RIGHT SIDE */
.info-box{
    display:flex;
    align-items:start;
    margin-bottom:25px;
}

.icon-circle{
    background:#d4af37;
    width:45px;
    height:45px;
    border-radius:12px;
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    font-weight:bold;
    margin-right:15px;
    font-size:20px;
}

/* make emoji white by forcing it via filter */
.icon-circle .bi {
    color: white;
    font-size: 20px;
}

/* Responsive */
@media(max-width:992px){
    .hero{
        padding:40px 20px;
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

<!-- HERO -->
<div class="hero">
    <h1>Contact & Support</h1>
    <p>Have questions or feedback? We'd love to hear from you. Our team is here to help make your experience better.</p>
</div>

<!-- MAIN SECTION -->
<div class="container main-section">
    <div class="row g-4">

        <!-- LEFT FORM -->
        <div class="col-md-6">
            <div class="card-custom">
                <h5 class="mb-4 fw-bold">Send us a Message</h5>

                <input type="text" class="form-control" placeholder="Your Name">
                <input type="email" class="form-control" placeholder="Email Address">
                <input type="text" class="form-control" placeholder="Subject">
                <textarea class="form-control" rows="5" placeholder="Tell us more about your inquiry..."></textarea>

                <button class="btn-send mt-3">Get in touch</button>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="col-md-6">
            <div class="card-custom">

                <h5 class="mb-4 fw-bold">Other Ways to Reach Us</h5>

                <div class="info-box">
                    <div class="icon-circle">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <strong>Email Support</strong><br>
                        support@salametak.app<br>
                        <small>We typically respond within 24 hours</small>
                    </div>
                </div>

                <div class="info-box">
                    <div class="icon-circle">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <strong>Phone Support</strong><br>
                        +20 123 456 7890<br>
                        <small>Sunday - Thursday, 9 AM - 6 PM</small>
                    </div>
                </div>

                <div class="info-box">
                    <div class="icon-circle">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <strong>Office Location</strong><br>
                        Cairo, Egypt<br>
                        <small>Serving drivers across Egypt</small>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>