<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salamtak - Sign Up</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">
<style>
html, body { height: 100%; margin: 0; font-family: 'Poppins', sans-serif; }
body {
    background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
                url('image/home.jpeg') no-repeat center center fixed;
    background-size: cover;
    display: flex; justify-content: center; align-items: center;
}
.signup-card {
    background: rgba(255,255,255,0.95);
    padding: 35px 30px; border-radius: 15px; width: 400px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3); text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.signup-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.35); }
.signup-card .logo img { width: 70px; margin-bottom: 15px; }
.signup-card h2 { font-weight: 700; margin-bottom: 20px; color: #1e2a44; }
.form-control {
    border-radius: 10px; margin-bottom: 15px; padding: 10px;
    border: 1px solid #ccc; transition: all 0.3s ease;
}
.form-control:hover, .form-control:focus {
    border-color: #1e2a44; box-shadow: 0 0 8px rgba(30,42,68,0.3);
    transform: translateY(-1px); outline: none;
}
.btn-submit, .btn-reset {
    width: 100%; padding: 10px; border-radius: 25px;
    font-weight: 500; border: none; cursor: pointer; margin-top: 10px; transition: 0.3s;
}
.btn-submit { background-color: #1e2a44; color: white; }
.btn-submit:hover { background-color: #2e3f66; color: white; }
.btn-reset { background-color: white; color: #1e2a44; border: 2px solid #1e2a44; }
.btn-reset:hover { background-color: #1e2a44; color: white; }
.error-msg { color: red; margin-bottom: 10px; font-size: 0.9rem; }
</style>
</head>
<body>

<div class="signup-card">
    <div class="logo"><img src="image/logof.png" alt="Salamtak Logo"></div>
    <h2>Sign Up</h2>
    <div id="error-msg" class="error-msg" style="display:none;"></div>
    <form id="signup-form">
        <input type="number" id="citizno" class="form-control" placeholder="National ID" required>
        <input type="text" id="citizname" class="form-control" placeholder="Name" required>
        <input type="text" id="citizadd" class="form-control" placeholder="Address" required>
        <input type="email" id="citizemail" class="form-control" placeholder="Email" required>
        <input type="number" id="citizphone" class="form-control" placeholder="Phone" required>
        <input type="password" id="pass" class="form-control" placeholder="Password" required>
        <input type="password" id="confirm_pass" class="form-control" placeholder="Confirm Password" required>
        <button type="submit" class="btn-submit">Sign Up</button>
        <button type="reset" class="btn-reset">Reset</button>
    </form>
</div>

<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
    import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
    import { getFirestore, doc, setDoc } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

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

    document.getElementById('signup-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorDiv = document.getElementById('error-msg');
        errorDiv.style.display = 'none';

        const nationalId = document.getElementById('citizno').value.trim();
        const name = document.getElementById('citizname').value.trim();
        const address = document.getElementById('citizadd').value.trim();
        const email = document.getElementById('citizemail').value.trim();
        const phone = document.getElementById('citizphone').value.trim();
        const pass = document.getElementById('pass').value;
        const confirmPass = document.getElementById('confirm_pass').value;

        if (pass !== confirmPass) {
            errorDiv.style.display = 'block';
            errorDiv.textContent = 'Passwords do not match.';
            return;
        }
        if (pass.length < 6) {
            errorDiv.style.display = 'block';
            errorDiv.textContent = 'Password must be at least 6 characters.';
            return;
        }

        // Use nationalId as fake email for Firebase Auth
        const fakeEmail = nationalId + '@salamtak.com';

        try {
            const userCredential = await createUserWithEmailAndPassword(auth, fakeEmail, pass);
            const uid = userCredential.user.uid;

            await setDoc(doc(db, "users", uid), {
                nationalId: nationalId,
                name: name,
                address: address,
                email: email,
                phone: phone,
                userType: 'user',
                createdAt: new Date().toISOString()
            });

            sessionStorage.setItem('loggedin', 'true');
            sessionStorage.setItem('uid', uid);
            sessionStorage.setItem('nationalId', nationalId);
            sessionStorage.setItem('name', name);
            sessionStorage.setItem('address', address);
            sessionStorage.setItem('phone', phone);

            window.location.href = 'home.php';
        } catch (err) {
            console.error('Signup error:', err);
            errorDiv.style.display = 'block';
            if (err.code === 'auth/email-already-in-use') {
                errorDiv.textContent = 'This National ID is already registered.';
            } else if (err.code === 'permission-denied' || err.message.includes('permission')) {
                errorDiv.textContent = 'Permission error. Please update Firestore security rules.';
            } else {
                errorDiv.textContent = 'Sign up failed: ' + err.message;
            }
        }
    });
</script>
</body>
</html>
