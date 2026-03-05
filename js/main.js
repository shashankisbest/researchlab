/**
 * ARED FACILITY - Main Interface Logic
 * Version: 1.0.2
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log("%c ARED SYSTEM ONLINE ", "background: #32ff7e; color: #000; font-weight: bold;");

    // --- Modal Logic ---
    const modal = document.getElementById('ctfModal');
    
    // Close modal when clicking outside of the content box
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    // Escape key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === "Escape" && modal.style.display === "block") {
            closeModal();
        }
    });
});

// Function to close the CTF modal
function closeModal() {
    const modal = document.getElementById('ctfModal');
    modal.style.display = 'none';
}

// Optional: Subtle terminal-style logging for user actions
function logAction(action) {
    const timestamp = new Date().toLocaleTimeString();
    console.log(`[${timestamp}] AUTH_LOG: ${action}`);
}