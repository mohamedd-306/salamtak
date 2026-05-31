<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salamtak - Submit Report</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
* { font-family: 'Segoe UI', sans-serif; }
body { margin: 0; padding-top: 80px; background: #f4f6fb; }

nav.navbar {
    position: fixed; top: 0; left: 0; width: 100%; z-index: 1000;
    background-color: #1e2a44;
}
nav .navbar-brand, nav .nav-link { color: white !important; transition: 0.3s; }
nav .nav-link:hover { color: #f4c430 !important; }

.page-title { text-align: center; margin: 30px 0 10px; color: #1e2a44; font-weight: 700; font-size: 28px; }
.page-sub { text-align: center; color: #666; margin-bottom: 30px; }

.report-card {
    background: white; border-radius: 15px; padding: 35px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08); max-width: 650px; margin: 0 auto 50px;
}

.form-label { font-weight: 500; color: #1e2a44; }
.form-control, .form-select {
    border-radius: 10px; border: 1px solid #ddd; padding: 10px 14px; transition: 0.3s;
}
.form-control:focus, .form-select:focus {
    border-color: #1e2a44; box-shadow: 0 0 8px rgba(30,42,68,0.2);
}

.btn-submit {
    background-color: #1e2a44; color: white; border: none;
    padding: 12px 30px; border-radius: 25px; font-weight: 500;
    width: 100%; margin-top: 10px; transition: 0.3s; font-size: 16px;
}
.btn-submit:hover { background-color: #2e3f66; }

.alert-not-logged {
    max-width: 650px; margin: 30px auto; border-radius: 12px;
}

#success-msg {
    display: none; background: #d4edda; color: #155724;
    border-radius: 10px; padding: 14px; text-align: center; margin-bottom: 15px;
}
#error-msg {
    display: none; background: #f8d7da; color: #721c24;
    border-radius: 10px; padding: 14px; text-align: center; margin-bottom: 15px;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="home.php">
            <img src="image/logof.png" width="50" class="me-2"> Salamtak
        </a>
        <div class="collapse navbar-collapse justify-content-center">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="features.php">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">My Reports</a></li>
            </ul>
        </div>
        <div id="nav-user" style="color:white; font-size:14px;"></div>
    </div>
</nav>

<div class="container">
    <div class="page-title">Submit a Report</div>
    <p class="page-sub">Report a road or public safety issue in your area</p>

    <div id="not-logged" class="alert alert-warning alert-not-logged text-center" style="display:none;">
        You must be <a href="login.php">logged in</a> to submit a report.
    </div>

    <div id="report-form-wrapper" class="report-card" style="display:none;">
        <div id="success-msg">Your report has been submitted successfully!</div>
        <div id="error-msg"></div>

        <form id="report-form">
            <div class="mb-3">
                <label class="form-label">Report Type</label>
                <select id="report-type" class="form-select" required>
                    <option value="" disabled selected>Select type...</option>
                    <option value="Road Damage">Road Damage</option>
                    <option value="Traffic Accident">Traffic Accident</option>
                    <option value="Flooding">Flooding</option>
                    <option value="Street Light Issue">Street Light Issue</option>
                    <option value="Blocked Road">Blocked Road</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" id="report-location" class="form-control" placeholder="e.g. Cairo, Nasr City, Street 9" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea id="report-desc" class="form-control" rows="4" placeholder="Describe the issue in detail..." required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Severity</label>
                <select id="report-severity" class="form-select" required>
                    <option value="" disabled selected>Select severity...</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Submit Report</button>
        </form>
    </div>
</div>

<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
    import { getAuth, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
    import { getFirestore, collection, addDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

    const firebaseConfig = {
        apiKey: "AIzaSyDY9lX8swlfKx3umnW57O5DA2Ka1Pdc0Fk",
        authDomain: "salmtak-6fffe.firebaseapp.com",
        projectId: "salmtak-6fffe",
        storageBucket: "salmtak-6fffe.firebasestorage.app",
        messagingSenderId: "1048763383483",
        appId: "1:1048763383483:web:f9a6140078484b5552f39e"
    };

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);
    const db = getFirestore(app);

    let currentUser = null;

    onAuthStateChanged(auth, (user) => {
        if (user) {
            currentUser = user;
            document.getElementById('report-form-wrapper').style.display = 'block';
            document.getElementById('not-logged').style.display = 'none';
            const name = sessionStorage.getItem('name') || '';
            document.getElementById('nav-user').textContent = name ? 'Hello, ' + name : '';
        } else {
            document.getElementById('report-form-wrapper').style.display = 'none';
            document.getElementById('not-logged').style.display = 'block';
        }
    });

    document.getElementById('report-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const successDiv = document.getElementById('success-msg');
        const errorDiv = document.getElementById('error-msg');
        successDiv.style.display = 'none';
        errorDiv.style.display = 'none';

        const type = document.getElementById('report-type').value;
        const location = document.getElementById('report-location').value.trim();
        const description = document.getElementById('report-desc').value.trim();
        const severity = document.getElementById('report-severity').value;

        try {
            await addDoc(collection(db, "reports"), {
                uid: currentUser.uid,
                nationalId: sessionStorage.getItem('nationalId') || '',
                name: sessionStorage.getItem('name') || '',
                type: type,
                location: location,
                description: description,
                severity: severity,
                status: 'Pending',
                createdAt: serverTimestamp()
            });

            successDiv.style.display = 'block';
            document.getElementById('report-form').reset();
        } catch (err) {
            errorDiv.style.display = 'block';
            errorDiv.textContent = 'Failed to submit report. Please try again.';
        }
    });
</script>
</body>
</html>
