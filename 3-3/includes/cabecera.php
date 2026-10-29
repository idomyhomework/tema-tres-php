<?php
// Barra superior común a las páginas protegidas. Necesita $usuario definido.
?>
<header class="barra">
    <div class="barra__usuario">
        <span class="barra__nombre"><?= htmlspecialchars($usuario['nombre']) ?></span>
        <span class="chip chip--<?= htmlspecialchars($usuario['rol']) ?>"><?= htmlspecialchars($usuario['rol']) ?></span>
    </div>
    <form action="logout.php" method="post">
        <button type="submit" class="boton-secundario">Cerrar sesión</button>
    </form>
</header>
