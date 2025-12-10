<div class="sidebar p-3">
    <div class="text-center mb-4">
        <div class="brand-icon" style="font-size:40px;">🐶</div>
        <h5 class="text-white mt-2">Clínica<br>Veterinaria</h5>
    </div>

    <a href="<?= $ruta ?>index.php" class="<?= ($pagina_actual == 'inicio') ? 'active' : '' ?>">
        Inicio
    </a>

    <a href="<?= $ruta ?>propietarios/lista.php" class="<?= ($pagina_actual == 'propietarios') ? 'active' : '' ?>">
        Propietarios
    </a>

    <a href="<?= $ruta ?>mascotas/lista.php" class="<?= ($pagina_actual == 'mascotas') ? 'active' : '' ?>">
        Mascotas
    </a>

    <a href="<?= $ruta ?>citas/lista.php" class="<?= ($pagina_actual == 'citas') ? 'active' : '' ?>">
    Citas
    </a>
    
    <a href="<?= $ruta ?>consultas/lista.php" class="<?= ($pagina_actual == 'consultas') ? 'active' : '' ?>">
        Consultas
    </a>

    <a href="<?= $ruta ?>expedientes/lista.php" class="<?= ($pagina_actual == 'expedientes') ? 'active' : '' ?>">
        Expedientes
    </a>

    <?php if (isset($_SESSION["rol"]) && $_SESSION["rol"] === 'admin'): ?>
        <a href="<?= $ruta ?>usuarios/lista.php" class="<?= ($pagina_actual == 'usuarios') ? 'active' : '' ?>">
            Usuarios
        </a>
    <?php endif; ?>

    <a href="#" data-bs-toggle="modal" data-bs-target="#modalCerrarSesion" class="text-danger mt-4">
        Cerrar sesión
    </a>
</div>

<div class="modal fade" id="modalCerrarSesion" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalLabel">¿Cerrar sesión?</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <p class="mb-0 fs-5">¿Estás seguro de que deseas salir del sistema?</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">No, cancelar</button>
        <a href="<?= $ruta ?>logout.php" class="btn btn-danger px-4">Sí, salir</a>
      </div>
    </div>
  </div>
</div>