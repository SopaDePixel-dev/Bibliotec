<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTec - Bienvenido</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); display: flex; justify-content: center; align-items: center; min-height: 100vh; }

        .container { width: 100%; max-width: 750px; padding: 20px; text-align: center; }
        h1 { font-size: 2.4rem; color: var(--primary); margin-bottom: 8px; font-weight: 700; }
        p.subtitle { color: var(--text-muted); margin-bottom: 40px; font-size: 1rem; }

        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        
        .card { background: var(--card-bg); padding: 35px 25px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; color: inherit; display: block; text-align: center; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.08); border-color: var(--primary); }

        .card h3 { font-size: 1.4rem; color: var(--primary); margin-bottom: 10px; }
        .card p { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 25px; line-height: 1.4; }

        .btn { background-color: var(--primary); color: white; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; display: inline-block; transition: background 0.2s; }
        .btn:hover { background-color: var(--primary-dark); }

        .card.student .btn { background-color: #047857; }
        .card.student .btn:hover { background-color: #065f46; }
        .card.student h3 { color: #047857; }
        .card.student:hover { border-color: #047857; }
    </style>
</head>
<body>

    <div class="container">
        <h1>📚 BiblioTec</h1>
        <p class="subtitle">Sistema Integral de Gestión Bibliotecaria</p>

        <div class="cards-grid">
            <!-- Acceso Personal / Admin -->
            <a href="login.php" class="card">
                <h3>👨‍💼 Personal y Administración</h3>
                <p>Acceso exclusivo para administradores y bibliotecarios del sistema escolar.</p>
                <span class="btn">Ingresar como Personal</span>
            </a>

            <!-- Acceso Estudiantes -->
            <a href="login_estudiante.php" class="card student">
                <h3>🎓 Portal de Estudiantes</h3>
                <p>Consulta tu historial, préstamos activos y el acervo de libros disponible.</p>
                <span class="btn">Ingresar como Alumno</span>
            </a>
        </div>
    </div>

</body>
</html>