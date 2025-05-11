document.addEventListener("DOMContentLoaded", () => {
    const functionEls = document.querySelectorAll(".function");
    const moduleImgs  = document.querySelectorAll('img[draggable="true"][data-module-id]');
    const citySlots   = document.querySelectorAll(".city-slot");

    functionEls.forEach((el) => {
        el.addEventListener("dragstart", (e) => {
            e.dataTransfer.setData("type", "function");
            e.dataTransfer.setData("function", el.dataset.function);
        });
    });

    moduleImgs.forEach((img) => {
        img.addEventListener("dragstart", (e) => {
            e.dataTransfer.setData("type", "module");
            e.dataTransfer.setData("module_id", img.dataset.moduleId);
        });
    });

    citySlots.forEach((slot) => {
        slot.addEventListener("dragover", (e) => {
            e.preventDefault();
            slot.classList.add("drag-over");
        });

        slot.addEventListener("dragleave", () => {
            slot.classList.remove("drag-over");
        });

        slot.addEventListener("drop", (e) => {
            e.preventDefault();
            slot.classList.remove("drag-over");

            const dropType = e.dataTransfer.getData("type");
            const slotId   = slot.dataset.slotId;

            if (dropType === "function") {
                const fn = e.dataTransfer.getData("function");
                slot.innerHTML = `<img src="/images/${fn}.png" alt="${fn}" class="assigned w-[60px]">`;
                slot.classList.remove("bg-gray-100");
                slot.classList.add("bg-red-200");
                return;
            }

            if (dropType === "module") {
                const moduleId = e.dataTransfer.getData("module_id");

                fetch("/simulatie/koppel-module", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({
                        module_id: moduleId,
                        slot_id  : slotId,
                    }),
                })
                    .then(async (resp) => {
                        let payload = {};
                        try { payload = await resp.json(); } catch (_) {}

                        if (resp.ok) {
                            location.reload();
                            return;
                        }

                        if (resp.status === 409 || payload.error === "max_reached") {
                            alert("Je hebt het maximum aantal van deze categorie bereikt.");
                            return;
                        }

                        if (resp.status === 422 || payload.error === "adjacent_invalid") {
                            alert("Je mag deze categorie niet langs een bepaalde andere categorie zetten.");
                            return;
                        }

                        alert(payload.message || "Er is iets misgegaan bij het koppelen (onbekende fout).");
                    })
                    .catch(() => {
                        alert("Er is iets misgegaan bij het koppelen (netwerk-/serverfout).");
                    });
            }
        });
    });
});

window.Alpine = di;
di.start();
