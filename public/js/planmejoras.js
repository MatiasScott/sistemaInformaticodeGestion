bootstrapPlanMejoras();

function bootstrapPlanMejoras() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', safeInitPlanMejoras, { once: true });
        return;
    }

    safeInitPlanMejoras();
}

function safeInitPlanMejoras() {
    if (window.__planMejorasInitialized) {
        return;
    }
    window.__planMejorasInitialized = true;

    try {
        initPlanSearch();
        initPlanActivities();
    } catch (error) {
        console.error('Error al inicializar planmejoras.js:', error);
    }
}

function initPlanSearch() {
    const searchInput = document.getElementById('planSearch');
    const rows = document.querySelectorAll('#planTable tbody tr');

    if (!searchInput || rows.length === 0) {
        return;
    }

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        rows.forEach(function (row) {
            const indicadorEl = row.querySelector('.plan-indicador');
            const indicador = indicadorEl ? indicadorEl.textContent.toLowerCase() : '';
            const actividad = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';

            row.style.display = (indicador.includes(searchTerm) || actividad.includes(searchTerm)) ? '' : 'none';
        });
    });
}

function initPlanActivities() {
    const actividadesList = document.getElementById('actividadesList');
    const addBtn = document.getElementById('addActividadBtn');
    const avanceInput = document.getElementById('avance');
    const estadoText = document.getElementById('estadoSugerido');

    if (!actividadesList || !addBtn || !avanceInput) {
        return;
    }

    function randomKey() {
        return 'act_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 8);
    }

    function setEstadoTexto(avance) {
        if (!estadoText) {
            return;
        }

        if (avance === 0) {
            estadoText.textContent = 'Estado sugerido: Pendiente';
        } else if (avance < 100) {
            estadoText.textContent = 'Estado sugerido: En Proceso';
        } else {
            estadoText.textContent = 'Estado sugerido: Finalizado';
        }
    }

    function updateAvance() {
        const rows = actividadesList.querySelectorAll('.actividad-item');
        let total = 0;
        let done = 0;

        rows.forEach(function (row) {
            const descInput = row.querySelector('.actividad-desc');
            const checkInput = row.querySelector('.actividad-check');

            if (!descInput || !checkInput) {
                return;
            }

            if (descInput.value.trim() === '') {
                return;
            }

            total += 1;
            if (checkInput.checked) {
                done += 1;
            }
        });

        const avance = total > 0 ? Math.round((done * 100) / total) : 0;
        
        // ACTUALIZAR campo avance del formulario para que se envíe
        if (avanceInput) {
            avanceInput.value = avance;
        }
        
        setEstadoTexto(avance);
    }

    function normalizeInitialActivities(parsed) {
        if (!parsed) {
            return [];
        }

        if (Array.isArray(parsed)) {
            return parsed.map(function (item, idx) {
                if (typeof item === 'string') {
                    return {
                        key: randomKey() + '_' + idx,
                        descripcion: item,
                        checked: false
                    };
                }

                if (item && typeof item === 'object') {
                    return {
                        key: item.key || (randomKey() + '_' + idx),
                        descripcion: item.descripcion || item.descripcion_actividad || '',
                        checked: !!item.checked
                    };
                }

                return {
                    key: randomKey() + '_' + idx,
                    descripcion: '',
                    checked: false
                };
            }).filter(function (item) {
                return String(item.descripcion || '').trim() !== '';
            });
        }

        if (typeof parsed === 'object' && Array.isArray(parsed.actividades)) {
            return normalizeInitialActivities(parsed.actividades);
        }

        return [];
    }

    function createRow(activity) {
        const key = activity && activity.key ? String(activity.key) : randomKey();
        const descripcion = activity && activity.descripcion ? String(activity.descripcion) : '';
        const checked = !!(activity && activity.checked);

        const row = document.createElement('div');
        row.className = 'actividad-item';
        row.dataset.locked = checked ? '1' : '0';

        row.innerHTML = [
            '<label class="actividad-check-wrap">',
            '<input type="checkbox" class="actividad-check" name="actividades_checked[]" value="' + key + '" ' + (checked ? 'checked disabled' : '') + '>',
            '<span>Completada</span>',
            '</label>',
            '<input type="hidden" name="actividades_key[]" value="' + key + '">',
            '<input type="text" class="actividad-desc" name="actividades_desc[]" placeholder="Descripción de la actividad" value="' + escapeHtml(descripcion) + '">',
            '<button type="button" class="btn btn-sm btn-danger actividad-remove" title="Eliminar actividad"><i class="fas fa-times"></i></button>'
        ].join('');

        const checkInput = row.querySelector('.actividad-check');
        const descInput = row.querySelector('.actividad-desc');
        const removeBtn = row.querySelector('.actividad-remove');

        if (checked && removeBtn) {
            removeBtn.disabled = true;
            removeBtn.classList.add('is-disabled');
        }

        checkInput.addEventListener('change', function () {
            if (row.dataset.locked === '1') {
                checkInput.checked = true;
                return;
            }

            if (checkInput.checked) {
                row.dataset.locked = '1';
                checkInput.disabled = true;
                if (removeBtn) {
                    removeBtn.disabled = true;
                    removeBtn.classList.add('is-disabled');
                }
            }

            updateAvance();
        });

        descInput.addEventListener('input', updateAvance);

        removeBtn.addEventListener('click', function () {
            if (row.dataset.locked === '1') {
                return;
            }
            row.remove();
            updateAvance();
        });

        return row;
    }

    function escapeHtml(value) {
        return value
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    let initialActivities = [];
    try {
        const raw = actividadesList.dataset.initial;
        if (raw) {
            const parsed = JSON.parse(raw);
            initialActivities = normalizeInitialActivities(parsed);
        }
    } catch (error) {
        console.warn('No se pudo leer actividades iniciales:', error);
    }

    if (initialActivities.length > 0) {
        initialActivities.forEach(function (activity) {
            actividadesList.appendChild(createRow(activity));
        });
    } else {
        actividadesList.appendChild(createRow(null));
    }

    addBtn.addEventListener('click', function () {
        actividadesList.appendChild(createRow(null));
        updateAvance();
    });

    window.planAgregarActividad = function () {
        actividadesList.appendChild(createRow(null));
        updateAvance();
    };

    const form = actividadesList.closest('form');
    if (form) {
        form.addEventListener('submit', function (event) {
            const rows = Array.from(actividadesList.querySelectorAll('.actividad-item'));
            
            let hasValidActivity = false;
            rows.forEach(function (row) {
                const desc = row.querySelector('.actividad-desc');
                const keyInput = row.querySelector('input[name="actividades_key[]"]');
                const checkInput = row.querySelector('input[name="actividades_checked[]"]');
                
                if (desc && keyInput && checkInput) {
                    const descValue = desc.value.trim();
                    
                    if (descValue === '') {
                        keyInput.disabled = true;
                        checkInput.disabled = true;
                    } else {
                        keyInput.disabled = false;
                        checkInput.disabled = false;
                        hasValidActivity = true;
                    }
                }
            });

            if (!hasValidActivity) {
                event.preventDefault();
                alert('Debe registrar al menos una actividad con descripción.');
                return false;
            }

            updateAvance();
        });
    }

    updateAvance();
}