<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
</head>
<body>

<h1>Selamat datang, {{ session('admin')->nama ?? 'Unknown' }}</h1>
<p>Email: {{ $admin->email }}</p>

</body>
</html>
