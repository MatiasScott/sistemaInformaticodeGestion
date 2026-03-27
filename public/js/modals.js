/* ============================================
   MODALES - JAVASCRIPT
   ============================================ */

class Modal {
    constructor(options = {}) {
        this.options = {
            title: 'Modal',
            message: '',
            type: 'info', // info, success, warning, error, confirm
            buttons: {
                confirm: { text: 'Aceptar', class: 'btn-primary' },
                cancel: { text: 'Cancelar', class: 'btn-secondary' }
            },
            onConfirm: () => {},
            onCancel: () => {},
            ...options
        };

        this.modal = null;
        this.overlay = null;
    }

    create() {
        // Crear overlay
        this.overlay = document.createElement('div');
        this.overlay.className = `modal-overlay active`;

        // Crear modal
        this.modal = document.createElement('div');
        this.modal.className = `modal modal-${this.options.type}`;

        // Header
        const header = document.createElement('div');
        header.className = 'modal-header';
        header.innerHTML = `<h2>${this.getIcon()} ${this.options.title}</h2>`;

        // Body
        const body = document.createElement('div');
        body.className = 'modal-body';
        body.innerHTML = this.options.message;

        // Footer
        const footer = document.createElement('div');
        footer.className = `modal-footer ${this.options.type === 'confirm' ? 'full-width' : ''}`;

        if (this.options.type === 'confirm') {
            const confirmBtn = document.createElement('button');
            confirmBtn.className = `btn ${this.options.buttons.confirm.class}`;
            confirmBtn.textContent = this.options.buttons.confirm.text;
            confirmBtn.onclick = () => {
                this.options.onConfirm();
                this.close();
            };

            const cancelBtn = document.createElement('button');
            cancelBtn.className = `btn ${this.options.buttons.cancel.class}`;
            cancelBtn.textContent = this.options.buttons.cancel.text;
            cancelBtn.onclick = () => {
                this.options.onCancel();
                this.close();
            };

            footer.appendChild(cancelBtn);
            footer.appendChild(confirmBtn);
        } else {
            const closeBtn = document.createElement('button');
            closeBtn.className = `btn ${this.options.buttons.confirm.class}`;
            closeBtn.textContent = this.options.buttons.confirm.text;
            closeBtn.onclick = () => this.close();

            footer.appendChild(closeBtn);
        }

        // Agregar elementos
        this.modal.appendChild(header);
        this.modal.appendChild(body);
        this.modal.appendChild(footer);
        this.overlay.appendChild(this.modal);

        // Cerrar al hacer click fuera del modal
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.close();
            }
        });

        document.body.appendChild(this.overlay);
    }

    getIcon() {
        const icons = {
            info: 'ℹ️',
            success: '✅',
            warning: '⚠️',
            error: '❌',
            confirm: '❓'
        };
        return icons[this.options.type] || '📌';
    }

    show() {
        if (!this.modal) {
            this.create();
        }
        this.overlay.classList.add('active');
    }

    close() {
        if (this.overlay) {
            this.overlay.remove();
            this.overlay = null;
            this.modal = null;
        }
    }
}

// ============================================
// FUNCIONES DE UTILIDAD
// ============================================

function showModal(title, message, type = 'info', onConfirm = null) {
    const options = {
        title,
        message,
        type,
        buttons: {
            confirm: { text: 'Aceptar', class: 'btn-primary' }
        }
    };

    if (onConfirm) {
        options.onConfirm = onConfirm;
    }

    const modal = new Modal(options);
    modal.show();
}

function showConfirmModal(title, message, onConfirm, onCancel = null) {
    const modal = new Modal({
        title,
        message,
        type: 'confirm',
        onConfirm,
        onCancel: onCancel || (() => {})
    });
    modal.show();
}

function showErrorModal(title, message) {
    return showModal(title, message, 'error');
}

function showSuccessModal(title, message, onConfirm = null) {
    return showModal(title, message, 'success', onConfirm);
}

function showWarningModal(title, message) {
    return showModal(title, message, 'warning');
}

// ============================================
// ALIAS PARA COMPATIBILIDAD
// ============================================

const Alert = {
    info: (title, message) => showModal(title, message, 'info'),
    success: (title, message) => showModal(title, message, 'success'),
    warning: (title, message) => showModal(title, message, 'warning'),
    error: (title, message) => showModal(title, message, 'error'),
    confirm: (title, message, onConfirm, onCancel) => showConfirmModal(title, message, onConfirm, onCancel)
};

// ============================================
// EJEMPLO DE USO
// ============================================
/*
// Modal simple
showModal('Éxito', 'El registro fue guardado correctamente', 'success');

// Modal de confirmación
showConfirmModal(
    'Eliminar',
    '¿Estás seguro de que quieres eliminar este registro?',
    () => {
        console.log('Confirmado');
    },
    () => {
        console.log('Cancelado');
    }
);

// Modal de error
showErrorModal('Error', 'Ha ocurrido un error al procesar tu solicitud');

// Usando el objeto Alert
Alert.success('Éxito', 'Operación completada');
Alert.confirm('Confirmar', '¿Continuar?', () => {}, () => {});
*/
