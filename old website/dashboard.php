<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salamtak - My Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
* { font-family: 'Poppins', sans-serif; }
body { margin: 0; padding-top: 80px; background: #f4f6fb; }

nav.navbar {
    position: fixed; top: 0; left: 0; width: 100%; z-index: 1000;
    background-color: #1e2a44;
}
nav .navbar-brand, nav .nav-link { color: white !important; transition: 0.3s; }
nav .nav-link:hover { background: white; color: #1e2a44 !important; padding: 5px 10px; border-radius: 5px; }

.page-title { text-align: center; margin: 30px 0 5px; color: #1e2a44; font-weight: 700; font-size: 28px; }
.page-sub { text-align: center; color: #666; margin-bottom: 30px; }

/* Stats row */
.stat-card {
    background: white; border-radius: 12px; padding: 20px 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.07); text-align: center;
}
.stat-card .stat-num { font-size: 36px; font-weight: 700; color: #1e2a44; }
.stat-card .stat-label { color: #888; font-size: 14px; }

/* Report cards */
.report-card {
    background: white; border-radius: 12px; padding: 20px 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.07); margin-bottom: 16px;
    border-left: 5px solid #1e2a44; transition: 0.3s;
}
.report-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }

.report-card .report-type { font-weight: 600; font-size: 17px; color: #1e2a44; }
.report-card .report-meta { font-size: 13px; color: #888; margin-top: 3px; }
.report-card .report-desc { margin-top: 10px; color: #444; font-size: 14px; }

/* Status badges */
.badge-pending   { background: #fff3cd; color: #856404; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 500; }
.badge-review    { background: #cce5ff; color: #004085; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 500; }
.badge-progress  { background: #d1ecf1; color: #0c5460; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 500; }
.badge-resolved  { background: #d4edda; color: #155724; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 500; }
.badge-rejected  { background: #f8d7da; color: #721c24; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 500; }

/* Severity dot */
.sev-low      { color: #28a745; font-weight: 600; }
.sev-medium   { color: #ffc107; font-weight: 600; }
.sev-high     { color: #fd7e14; font-weight: 600; }
.sev-critical { color: #dc3545; font-weight: 600; }

.empty-state {
    text-align: center; padding: 60px 20px; color: #aaa;
}
.empty-state .icon { font-size: 60px; margin-bottom: 15px; }

#loading { text-align: center; padding: 40px; color: #888; }

.filter-bar { margin-bottom: 20px; }
.filter-bar select { border-radius: 20px; border: 1px solid #ddd; padding: 6px 16px; font-size: 14px; }

.btn-new-report {
    background: #1e2a44; color: white; border: none;
    padding: 10px 24px; border-radius: 25px; font-weight: 500;
    text-decoration: none; transition: 0.3s; display: inline-block;
}
.btn-new-report:hover { background: #2e3f66; color: white; }

.not-logged { max-width: 600px; margin: 40px auto; border-radius: 12px; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
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
                <li class="nav-item"><a class="nav-link" href="report.php">Submit Report</a></li>
            </ul>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <span id="nav-user" style="color:white; font-size:14px;"></span>
            <button id="btn-logout" onclick="logOut()" style="display:none; background:transparent; border:1px solid white; color:white; border-radius:20px; padding:5px 14px; font-size:13px; cursor:pointer;">Logout</button>
        </div>
    </div>
</nav>

<div class="container">

    <div id="not-logged" class="alert alert-warning not-logged text-center" style="display:none;">
        You must be <a href="login.php">logged in</a> to view your reports.
    </div>

    <div id="dashboard-content" style="display:none;">
        <div class="page-title">My Reports</div>
        <p class="page-sub">Track all your submitted reports and their current status</p>

        <!-- Stats -->
        <div class="row g-3 mb-4" id="stats-row">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-num" id="stat-total">0</div>
                    <div class="stat-label">Total Reports</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-num" id="stat-pending" style="color:#856404;">0</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-num" id="stat-progress" style="color:#0c5460;">0</div>
                    <div class="stat-label">In Progress</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-num" id="stat-resolved" style="color:#155724;">0</div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>
        </div>

        <!-- Filter + New Report -->
        <div class="d-flex justify-content-between align-items-center filter-bar flex-wrap gap-2">
            <select id="filter-status" onchange="renderReports()">
                <option value="all">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Under Review">Under Review</option>
                <option value="In Progress">In Progress</option>
                <option value="Resolved">Resolved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <a href="report.php" class="btn-new-report">+ New Report</a>
        </div>

        <div id="loading">Loading your reports...</div>
        <div id="reports-list"></div>
    </div>
</div>

<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
    import { getAuth, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
    import { getFirestore, collection, query, where, orderBy, onSnapshot } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

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

    window.allReports = [];

    window.logOut = async () => {
        await signOut(auth);
        sessionStorage.clear();
        window.location.href = 'login.php';
    };

    window.renderReports = () => {
        const filter = document.getElementById('filter-status').value;
        const list = document.getElementById('reports-list');
        const filtered = filter === 'all' ? window.allReports : window.allReports.filter(r => r.status === filter);

        if (filtered.length === 0) {
            list.innerHTML = `<div class="empty-state"><div class="icon">📋</div><p>No reports found.</p><a href="report.php" class="btn-new-report">Submit your first report</a></div>`;
            return;
        }

        list.innerHTML = filtered.map(r => {
            const statusBadge = {
                'Pending':      `<span class="badge-pending">⏳ Pending</span>`,
                'Under Review': `<span class="badge-review">🔍 Under Review</span>`,
                'In Progress':  `<span class="badge-progress">🔧 In Progress</span>`,
                'Resolved':     `<span class="badge-resolved">✅ Resolved</span>`,
                'Rejected':     `<span class="badge-rejected">❌ Rejected</span>`
            }[r.status] || `<span class="badge-pending">${r.status}</span>`;

            const sevClass = { Low: 'sev-low', Medium: 'sev-medium', High: 'sev-high', Critical: 'sev-critical' }[r.severity] || '';
            const date = r.createdAt ? new Date(r.createdAt.seconds * 1000).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }) : 'N/A';

            return `
            <div class="report-card">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="report-type">${r.type}</div>
                        <div class="report-meta">📍 ${r.location} &nbsp;|&nbsp; 📅 ${date} &nbsp;|&nbsp; Severity: <span class="${sevClass}">${r.severity}</span></div>
                    </div>
                    <div>${statusBadge}</div>
                </div>
                <div class="report-desc">${r.description}</div>
            </div>`;
        }).join('');
    };

    onAuthStateChanged(auth, (user) => {
        if (user) {
            document.getElementById('dashboard-content').style.display = 'block';
            document.getElementById('not-logged').style.display = 'none';
            document.getElementById('btn-logout').style.display = 'inline-block';
            const name = sessionStorage.getItem('name') || '';
            document.getElementById('nav-user').textContent = name ? 'Hello, ' + name : '';

            // Real-time listener for this user's reports
            const q = query(
                collection(db, "reports"),
                where("uid", "==", user.uid),
                orderBy("createdAt", "desc")
            );

            onSnapshot(q, (snapshot) => {
                window.allReports = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));

                // Update stats
                document.getElementById('stat-total').textContent = window.allReports.length;
                document.getElementById('stat-pending').textContent = window.allReports.filter(r => r.status === 'Pending').length;
                document.getElementById('stat-progress').textContent = window.allReports.filter(r => r.status === 'In Progress').length;
                document.getElementById('stat-resolved').textContent = window.allReports.filter(r => r.status === 'Resolved').length;

                document.getElementById('loading').style.display = 'none';
                window.renderReports();
            });
        } else {
            document.getElementById('dashboard-content').style.display = 'none';
            document.getElementById('not-logged').style.display = 'block';
        }
    });
</script>
</body>
</html>
