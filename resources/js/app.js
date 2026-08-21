document.addEventListener('livewire:init', () => {
    Livewire.on('show-bs-modal', ({ id }) => {
        const element = document.getElementById(id);

        if (element) {
            bootstrap.Modal.getOrCreateInstance(element).show();
        }
    });

    Livewire.on('hide-bs-modal', ({ id }) => {
        const element = document.getElementById(id);

        if (element) {
            bootstrap.Modal.getInstance(element)?.hide();
        }
    });

    Livewire.on('toast', ({ variant, text }) => {
        window.toast?.[variant]?.(text);
    });
});
