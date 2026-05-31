<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salamtak - About</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>

body{
    margin:0;
    font-family: 'Segoe UI', sans-serif;
    background:#f6f3ee;
    padding-top:80px;
}

/* ===== NAVBAR (FIXED + DARK COLOR) ===== */
nav.navbar {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 9999;
    background: #1e2a44 !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.navbar-brand {
    color: white !important;
    font-weight: 700;
    font-size: 22px;
}

.navbar .nav-link {
    color: white !important;
    font-weight: 500;
    margin-right: 20px;
}

.navbar .nav-link:hover {
    color: #f4c430 !important;
}

.download-btn {
    background: white;
    color: #1e2a44;
    padding: 8px 18px;
    border-radius: 40px;
    margin-left: 10px;
    font-weight: 500;
    text-decoration: none !important;
}

/* ===== CONTENT ===== */
.problem-section{
    padding:100px 80px;
}

.badge-problem{
    background:#f4c430;
    color:#1e2a44;
    padding:6px 18px;
    border-radius:20px;
    font-size:14px;
    font-weight:600;
    display:inline-block;
    margin-bottom:20px;
}

.problem-title{
    font-family:'Playfair Display', serif;
    font-size:55px;
    font-weight:700;
    color:#1e2a44;
    line-height:1.2;
}

.problem-text{
    color:#666;
    font-size:16px;
    margin-top:20px;
    max-width:500px;
}

/* ===== CARDS ===== */
.stat-card{
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    margin-bottom:25px;
    display:flex;
    gap:15px;
}

.icon-circle{
    background:#f4c430;
    width:45px;
    height:45px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    color:white;
}

.stat-card h4{
    font-weight:700;
    color:#1e2a44;
}

.stat-card p{
    font-size:14px;
    color:#666;
    margin:0;
}

/* ===== BLUE SECTION ===== */
.blue-section{
    padding:60px 80px 120px 80px;
}

.blue-card{
    background:#243e63;
    color:white;
    border-radius:25px;
    padding:60px;
    box-shadow:0 15px 35px rgba(0,0,0,0.15);
}

.blue-card h2{
    font-weight:700;
}

.blue-card p{
    color:#d6dbe6;
    max-width:700px;
}

.explore-btn{
    background:#f4c430;
    color:#1e2a44;
    padding:12px 28px;
    border-radius:30px;
    font-weight:600;
    text-decoration:none;
    display:inline-block;
    margin-top:20px;
}

.explore-btn:hover{
    transform:scale(1.05);
}

/* Responsive */
@media(max-width:992px){
    .problem-section,
    .blue-section{
        padding:60px 30px;
    }
    .problem-title{
        font-size:38px;
    }
}

</style>
</head>

<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.php">
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

<!-- ===== PROBLEM SECTION ===== -->
<section class="problem-section">
<div class="container-fluid">
<div class="row align-items-center">

<div class="col-lg-6">
<div class="badge-problem">The Problem</div>

<h1 class="problem-title">
Every day, Egyptian drivers face unexpected hazards that put lives at risk
</h1>

<p class="problem-text">
From potholes and accidents to blocked roads and construction zones, 
our roads present countless challenges that affect safety, time, and peace of mind.
</p>
</div>

<div class="col-lg-6">

<div class="stat-card">
<div class="icon-circle">!</div>
<div>
<h4>1M+</h4>
<p><strong>Road Incidents Yearly</strong><br>
Thousands of hazards affect Egyptian drivers daily</p>
</div>
</div>

<div class="stat-card">
<div class="icon-circle">⏱</div>
<div>
<h4>45min</h4>
<p><strong>Average Delay</strong><br>
Unexpected closures cause major delays</p>
</div>
</div>

<div class="stat-card">
<div class="icon-circle">⚠</div>
<div>
<h4>78%</h4>
<p><strong>Lack Awareness</strong><br>
Drivers discover hazards too late</p>
</div>
</div>

</div>
</div>
</div>
</section>

<!-- ===== BLUE SECTION ===== -->
<section class="blue-section">
<div class="container">
<div class="blue-card">

<h2>Salamtak Changes Everything</h2>

<p>
Our mobile application empowers drivers to report hazards instantly, 
receive real-time updates, and help build safer roads across Egypt.
</p>

<a href="features.php" class="explore-btn">Explore Features →</a>

</div>
</div>
</section>

</body>
</html>