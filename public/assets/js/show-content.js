/**
 * questo file viene usato all'interno di index.php per mostrare il contenuto di un romanzo in un modal quando si clicca su una riga della tabella.
 */

const userIsPremium = window.userIsPremium || 0;

const modal = document.getElementById('novel-modal');
const closeBtn = document.querySelector('.close-btn');
const modalTitle = document.getElementById('modal-title');
const modalAuthor = document.getElementById('modal-author');
const modalBody = document.getElementById('modal-body');
const rows = document.querySelectorAll('.novel-row');
rows.forEach((row) => {
    row.addEventListener('click', () => {
        const t = row.dataset.title;
        const a = row.dataset.email;
        const p = Number(row.dataset.premium) || 0;
        const c = row.dataset.content;

        // il link viene ricavato dal file_stored_name del db.
        const link = row.dataset.link;

        modalTitle.textContent = t;
        modalAuthor.textContent = a;

        if (p && !userIsPremium) {
            modalBody.innerHTML = `
                <p style="color:#a33; text-align:center; margin-top:1rem;">
                    This novel is for premium users only.
                </p>
                <p style="text-align:center;">
                    <a href="become_premium.php" class="upgrade-link">Become a Premium Member</a>
                </p>
            `;
        } else {
            if (!c) {
                modalBody.innerHTML = `
                    <p style="text-align:center; margin-top:1rem;">
                        <a href="${link}" download class="download-link">Download PDF</a>
                    </p>
                `;
            } else {
                modalBody.innerHTML = `
                    <div class="story-text">${c}</div>
                `;
            }
        }

        modal.style.display = 'flex';
    });
});

closeBtn.onclick = () => (modal.style.display = 'none');
modal.onclick = (e) => {
    if (e.target === modal) modal.style.display = 'none';
};
