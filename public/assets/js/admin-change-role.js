/**
 * questo file serve dentro admin.php e viene usato per cambiare il ruolo di un utente.
 * l'admin clicca su una riga. il pointer è messo nel css. si apre una modal basica con tre bottoni rappresentanti i ruoli.
 * l'admin clicca su un ruolo e poi conferma. viene fatto un redirect a update_role.php con i parametri userId e newRole.
 *  */

const modal = document.getElementById('user-modal');
const closeBtn = document.querySelector('.close-btn');
const modalTitle = document.getElementById('modal-title');
const modalBody = document.getElementById('modal-body');
const rows = document.querySelectorAll('.user-row');
const csrfToken = document.querySelector('input[name="csrf_token"]').value;

rows.forEach((row) => {
    row.addEventListener('click', () => {
        const email = row.dataset.email;
        const role = row.dataset.role;
        const userId = row.dataset.userId;

        modalTitle.textContent = email;

        const roles = ['User', 'Premium', 'Admin'];

        let selectedRole = role;

        modalBody.innerHTML = `
            <div class="role-selector">
                ${roles
                    .map(
                        (r) => `
                    <button class="role-btn ${r === role ? 'button-primary' : 'button-secondary'}" data-role="${r}">
                        ${r}
                    </button>
                `,
                    )
                    .join('')}
            </div>
            <div style="text-align:center; margin-top:1.5rem;">
                <form id="update-role-form" action="update_role.php" method="POST">
                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                    <input type="hidden" name="userId" value="${userId}">
                    <input type="hidden" name="newRole" id="newRoleInput" value="${selectedRole}">
                    <button type="submit" class="button-primary">Confirm</button>
                </form>
            </div>
        `;

        const newRoleInput = document.getElementById('newRoleInput');
        modalBody.querySelectorAll('.role-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                selectedRole = btn.dataset.role;
                newRoleInput.value = selectedRole; // Aggiorna l'input nascosto

                modalBody.querySelectorAll('.role-btn').forEach((b) => {
                    b.classList.replace('button-primary', 'button-secondary');
                });
                btn.classList.replace('button-secondary', 'button-primary');
            });
        });

        modal.style.display = 'flex';
    });
});
