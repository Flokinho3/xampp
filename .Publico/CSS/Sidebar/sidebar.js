const sidebar = document.querySelector(".Sidebar");
const container = document.querySelector(".Container");

// Detecta quando o mouse se aproxima da borda esquerda
document.addEventListener("mousemove", (event) => {
if (event.clientX <= 50) {
    // Expande o sidebar
    sidebar.style.width = "200px";
    container.style.marginLeft = "200px";
} else if (event.clientX > 200) {
    // Recolhe o sidebar se o mouse se afastar
    sidebar.style.width = "50px";
    container.style.marginLeft = "50px";
    }
});
