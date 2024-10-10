// Debug
let version = '1.0.4';
console.log('JS cargado correctamente. version:', version);

// Función de enmascarado de RUT, mostrando solo los primeros 4 caracteres
function enmascararRut(rut) {
    return rut.slice(0, 4) + '*****';
}

// Función para mostrar alertas de SweetAlert
function mostrarAlerta(icon, title, text) {
    Swal.fire({ icon, title, text, confirmButtonText: 'Aceptar' });
}

// Crear Sorteo
const crearSorteoForm = document.getElementById('crearSorteoForm');
if (crearSorteoForm) {
    crearSorteoForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/sorteos/crear', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/sorteos/editar/' + data.sorteo_id;
                } else {
                    mostrarAlerta('error', 'Error', data.message);
                }
            })
            .catch(() => mostrarAlerta('error', 'Error', 'Error al crear el sorteo.'));
    });
}

// Confirmar logout
const logoutLink = document.querySelector('a.nav-link[href="/logout"]');
if (logoutLink) {
    logoutLink.addEventListener('click', function (event) {
        event.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Vas a cerrar la sesión!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) window.location.href = '/logout';
        });
    });
}

// Seleccionar tipo de sorteo (crear/editar)
function mostrarSiguientePaso() {
    const opcionSeleccionada = document.querySelector('input[name="sorteoOption"]:checked').value;
    document.getElementById('crearSorteo').style.display = opcionSeleccionada === 'crear' ? 'block' : 'none';
    document.getElementById('editarSorteo').style.display = opcionSeleccionada === 'editar' ? 'block' : 'none';
}

// Función para cargar y actualizar participantes
function actualizarParticipantes(participantes) {
    const listaParticipantesDiv = document.getElementById('listaParticipantes');
    const totalParticipantesSpan = document.getElementById('totalParticipantes');

    totalParticipantesSpan.textContent = `(${participantes.length})`;
    listaParticipantesDiv.innerHTML = participantes.length > 0
        ? `
            <ul class="list-group list-unstyled">
                ${participantes.map(p => `
                    <li class="list-group-item">
                        ${p.nombre} - ${enmascararRut(p.rut)}
                        ${!p.baneado 
                            ? `<button class="btn btn-warning btn-sm banear-participante" data-participante-id="${p.id}">Banear</button>`
                            : `<span class="text-danger">(Baneado)</span>
                               <button class="btn btn-secondary btn-sm desbanear-participante" data-participante-id="${p.id}">Desbanear</button>`
                        }
                    </li>
                `).join('')}
            </ul>
        `
        : '<p>No hay participantes cargados.</p>';
}

// Cargar participantes desde archivo CSV
const cargarParticipantesForm = document.getElementById('cargarParticipantesForm');
if (cargarParticipantesForm) {
    cargarParticipantesForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/sorteos/cargar-participantes', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.message });
                    cargarParticipantesForm.reset();
                    actualizarParticipantes(data.participantes);
                    if (data.participantes.length > 0) {
                        document.getElementById('realizarSorteo').style.display = 'block';
                    }
                } else {
                    mostrarAlerta('error', 'Error', data.message);
                }
            })
            .catch(() => mostrarAlerta('error', 'Error', 'Error al cargar los participantes.'));
    });
}

// Función para realizar el sorteo con cuenta regresiva
const realizarSorteoForm = document.getElementById('realizarSorteoForm');
if (realizarSorteoForm) {
    realizarSorteoForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        Swal.fire({
            title: 'Preparando el sorteo...',
            html: 'El sorteo comenzará en <b>3</b> segundos.',
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
                const b = Swal.getHtmlContainer().querySelector('b');
                let timeLeft = 3;
                const interval = setInterval(() => b.textContent = timeLeft--, 1000);
                setTimeout(() => clearInterval(interval), 3000);
            }
        }).then(() => {
            fetch('/sorteos/realizar-sorteo', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: '¡Felicitaciones!', html: data.html }).then(() => location.reload());
                    } else {
                        mostrarAlerta('error', 'Error', data.message);
                    }
                })
                .catch(() => mostrarAlerta('error', 'Error', 'Error al realizar el sorteo.'));
        });
    });
}

// Manejar la anulación de ganador
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('anular-ganador')) {
        e.preventDefault();
        const participanteId = e.target.getAttribute('data-participante-id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto anulará al ganador y seleccionará un nuevo ganador.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, reemplazar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('/sorteos/anular-ganador', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sorteo_id: sorteoId, participante_id: participanteId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Reemplazado!', data.message, 'success').then(() => location.reload());
                        } else {
                            mostrarAlerta('error', 'Error', data.message);
                        }
                    })
                    .catch(() => mostrarAlerta('error', 'Error', 'Ha ocurrido un error.'));
            }
        });
    }
});

// Función para verificar la existencia de un sorteo antes de la edición
function verificarSorteoExistente(event) {
    event.preventDefault();
    const sorteoId = document.getElementById('sorteo_id').value;

    fetch(`/sorteos/verificar/${sorteoId}`, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                window.location.href = `/sorteos/editar/${sorteoId}`;
            } else {
                mostrarAlerta('error', 'Sorteo no encontrado', 'El sorteo seleccionado no existe o fue eliminado.');
            }
        })
        .catch(() => mostrarAlerta('error', 'Error', 'No se pudo verificar el sorteo. No existe o hubo un error.'));
}

// Agregar el event listener al formulario de edición de sorteos
const editarSorteoForm = document.getElementById('editarSorteoForm');
if (editarSorteoForm) {
    editarSorteoForm.addEventListener('submit', verificarSorteoExistente);
}


// Manejar la publicación del sorteo
document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'publicarSorteo') {
        e.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto hará público el resultado del sorteo.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, publicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/sorteos/publicar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ sorteo_id: sorteoId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('¡Publicado!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Ha ocurrido un error.', 'error');
                });
            }
        });
    }

    if (e.target && e.target.id === 'despublicarSorteo') {
        e.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto ocultará el resultado del sorteo al público.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, despublicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/sorteos/despublicar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ sorteo_id: sorteoId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('¡Despublicado!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Ha ocurrido un error.', 'error');
                });
            }
        });
    }
});