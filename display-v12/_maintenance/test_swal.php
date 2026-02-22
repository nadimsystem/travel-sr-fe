<?php
// Simple page to test if Swal is loaded
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>Testing SweetAlert</h1>
    <button onclick="testSwal()">Click Me</button>
    <script>
        function testSwal() {
            try {
                Swal.fire('Hello world!');
                console.log('Swal is working');
            } catch (e) {
                console.error('Swal error', e);
            }
        }
    </script>
</body>
</html>
