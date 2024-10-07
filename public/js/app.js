// Crear Sorteo
const crearSorteoForm = document.getElementById('crearSorteoForm');

if (crearSorteoForm) {
    crearSorteoForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Evitar que el formulario recargue la página

        const formData = new FormData(this);

        fetch('/sorteos/crear', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirigir al usuario a la página de edición del sorteo
                    window.location.href = '/sorteos/editar/' + data.sorteo_id;
                } else {
                    // Mostrar mensaje de error con SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ha ocurrido un error al crear el sorteo.',
                });
            });
    });
}


// Confirmar logout
const logoutLink = document.querySelector('a.nav-link[href="/sorteos/logout"]');

if (logoutLink) {
    logoutLink.addEventListener('click', function (event) {
        event.preventDefault(); // Prevenir la acción por defecto de redirigir

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Vas a cerrar la sesión!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/sorteos/logout'; // Redirigir solo si se confirma
            }
        });
    });
}


// Configuraciónde sorteos

function mostrarSiguientePaso() {
    const opcionSeleccionada = document.querySelector('input[name="sorteoOption"]:checked').value;

    // Ocultar ambas secciones primero
    document.getElementById('crearSorteo').style.display = 'none';
    document.getElementById('editarSorteo').style.display = 'none';

    // Mostrar la sección correspondiente
    if (opcionSeleccionada === 'crear') {
        document.getElementById('crearSorteo').style.display = 'block';
    } else if (opcionSeleccionada === 'editar') {
        document.getElementById('editarSorteo').style.display = 'block';
    }
}


// Cargar participantes
const cargarParticipantesForm = document.getElementById('cargarParticipantesForm');

if (cargarParticipantesForm) {
    cargarParticipantesForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/sorteos/cargar-participantes', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message,
                    });

                    // Limpiar el campo de selección de archivo
                    cargarParticipantesForm.reset();

                    // Actualizar lista de participantes y total
                    actualizarParticipantes(data.participantes);

                    // Mostrar sección "Realizar Sorteo" si hay participantes
                    if (data.participantes.length > 0) {
                        document.getElementById('realizarSorteo').style.display = 'block';
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ha ocurrido un error al cargar los participantes.',
                });
            });
    });
}

function actualizarParticipantes(participantes) {
    const listaParticipantesDiv = document.getElementById('listaParticipantes');
    const totalParticipantesSpan = document.getElementById('totalParticipantes');

    // Actualizar total de participantes
    totalParticipantesSpan.textContent = `(${participantes.length})`;

    // Actualizar lista de participantes
    if (participantes.length > 0) {
        let html = '<ul>';
        participantes.forEach(participante => {
            html += `<li>${participante.nombre} - ${participante.rut}</li>`;
        });
        html += '</ul>';
        listaParticipantesDiv.innerHTML = html;
    } else {
        listaParticipantesDiv.innerHTML = '<p>No hay participantes cargados.</p>';
    }
}


const realizarSorteoForm = document.getElementById('realizarSorteoForm');

if (realizarSorteoForm) {
    realizarSorteoForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Mostrar cuenta regresiva
        let timerInterval;
        Swal.fire({
            title: 'Preparando el sorteo...',
            html: 'El sorteo comenzará en <b></b> segundos.',
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
                const b = Swal.getHtmlContainer().querySelector('b');
                let timeLeft = 3;
                timerInterval = setInterval(() => {
                    b.textContent = timeLeft;
                    timeLeft--;
                }, 1000);
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        }).then(() => {
            // Realizar el sorteo después de la cuenta regresiva
            fetch('/sorteos/realizar-sorteo', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mostrar ganador(es) con animación
                        Swal.fire({
                            icon: 'success',
                            title: '¡Felicitaciones!',
                            html: data.html,
                            showConfirmButton: true
                        }).then(() => {
                            // Recargar la página para reflejar el estado cerrado
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ha ocurrido un error al realizar el sorteo.',
                    });
                });
        });
    });
}


// Manejar la anulación de ganadores
document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('anular-ganador')) {
        e.preventDefault();
        const participanteId = e.target.getAttribute('data-participante-id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto anulará al ganador y seleccionará un nuevo ganador.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, reemplazar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar solicitud para anular ganador
                fetch('/sorteos/anular-ganador', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        sorteo_id: sorteoId,
                        participante_id: participanteId
                    })

                }).then(// Mostrar en consola los datos de sorteo_id si están
                    console.log(sorteoId)
                )
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Reemplazado!', data.message, 'success').then(() => {
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

// Manejar el baneo de participantes
document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('banear-participante')) {
        e.preventDefault();
        const participanteId = e.target.getAttribute('data-participante-id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto baneará al participante y no podrá ser incluido en futuros sorteos.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, banear',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar solicitud para banear participante
                fetch('/sorteos/participantes/banear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ participante_id: participanteId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Baneado!', data.message, 'success').then(() => {
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
    // Manejar el desbaneo de participantes
    if (e.target && e.target.classList.contains('desbanear-participante')) {
        e.preventDefault();
        const participanteId = e.target.getAttribute('data-participante-id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto desbaneará al participante y podrá ser incluido en futuros sorteos.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desbanear',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar solicitud para desbanear participante
                fetch('/participantes/desbanear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ participante_id: participanteId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Desbaneado!', data.message, 'success').then(() => {
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
