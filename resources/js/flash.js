let library = null;

const load = async () => {
    library ??= (await import('sweetalert2')).default;

    return library;
};

const escape = (value) =>
    String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');

const DURATIONS = { success: 2400, error: 5200, warning: 4200, info: 3600 };

const fire = async (item) => {
    const Swal = await load();

    const options = {
        title: item.title,
        icon: item.type,
        position: 'center',
        timer: DURATIONS[item.type] ?? DURATIONS.info,
        timerProgressBar: true,
        showConfirmButton: false,
        allowOutsideClick: true,
        backdrop: true,
        animation: true,
        animationDuration: 300,
        customClass: {
            popup: 'swal-modal',
            icon: 'swal-modal__icon',
            title: 'swal-modal__title',
            htmlContainer: 'swal-modal__body',
        },
        didOpen: (popup) => {
            popup.addEventListener('mouseenter', () => Swal.stopTimer());
            popup.addEventListener('mouseleave', () => Swal.resumeTimer());
        },
    };

    if (item.details.length > 0) {
        options.html = `<ul class="swal-list">${item.details.map((line) => `<li>${escape(line)}</li>`).join('')}</ul>`;
    }

    await Swal.fire(options);
};

export const showFlashModals = async (items) => {
    for (const item of items) {
        await fire(item);
    }
};
