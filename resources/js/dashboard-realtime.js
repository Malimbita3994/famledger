import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const dashboardEl = document.getElementById('famledger-dashboard-broadcast');
const treeEl = document.getElementById('famledger-tree-broadcast');
const profileEl = document.getElementById('famledger-profile-broadcast');
const key = import.meta.env.VITE_REVERB_APP_KEY;
const host = import.meta.env.VITE_REVERB_HOST;
const port = import.meta.env.VITE_REVERB_PORT;
const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

function datasetEnabled(el) {
    return el?.dataset?.enabled === '1';
}

const familyId =
    dashboardEl?.dataset?.familyId ||
    treeEl?.dataset?.familyId ||
    profileEl?.dataset?.familyId ||
    '';

const connect =
    key &&
    host &&
    familyId &&
    (datasetEnabled(dashboardEl) || datasetEnabled(treeEl) || datasetEnabled(profileEl));

function applyProfileUpdatedPayload(e) {
    const hi = e?.health_index;
    if (hi && typeof hi.emoji === 'string') {
        const healthIndexDiv = document.getElementById('family-health-index');
        if (healthIndexDiv) {
            healthIndexDiv.innerHTML = `${hi.emoji} ${hi.text ?? ''}`;
            healthIndexDiv.classList.add('animate-pulse');
            setTimeout(() => healthIndexDiv.classList.remove('animate-pulse'), 1000);
        }
        const healthDesc = document.getElementById('family-health-desc');
        if (healthDesc && typeof hi.description === 'string') {
            healthDesc.textContent = hi.description;
        }
    }

    const lt = e?.ledger_totals;
    if (lt && typeof lt === 'object') {
        const block = document.querySelector('.fin-totals-block[data-fam-currency-code]');
        const code = block?.dataset?.famCurrencyCode || '';
        const fmt = (n) =>
            Number(n ?? 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        const incomeEl = document.getElementById('fam-profile-total-income');
        const expEl = document.getElementById('fam-profile-total-expenses');
        const balEl = document.getElementById('fam-profile-balance');
        if (incomeEl) {
            incomeEl.textContent = `${code} ${fmt(lt.total_income)}`;
        }
        if (expEl) {
            expEl.textContent = `${code} ${fmt(lt.total_expenses)}`;
        }
        if (balEl) {
            const w = Number(lt.wallet_balance_total ?? 0);
            balEl.textContent = `${code} ${fmt(w)}`;
            balEl.classList.toggle('text-green-600', w >= 0);
            balEl.classList.toggle('text-destructive', w < 0);
        }
    }

    if (Array.isArray(e?.leaderboard)) {
        e.leaderboard.forEach((user) => {
            const avatarImg = document.querySelector(`.user-avatar[data-id="${user.id}"]`);
            if (avatarImg) {
                avatarImg.classList.remove('ring-success', 'ring-warning', 'ring-danger');
                avatarImg.classList.add(`ring-${user.budget_status}`);
                const pointsSpan = document.querySelector(`.user-points[data-id="${user.id}"]`);
                if (pointsSpan) {
                    pointsSpan.innerText = user.points;
                }
            }
        });
    }
}

if (connect) {
    const wsPort = port ? Number(port) : 80;

    if (!window.Echo) {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key,
            wsHost: host,
            wsPort,
            wssPort: wsPort,
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    }

    const channel = window.Echo.private(`family.${familyId}`);

    if (datasetEnabled(dashboardEl) || datasetEnabled(profileEl)) {
        channel.listen('.profile.updated', applyProfileUpdatedPayload);
    }

    if (datasetEnabled(dashboardEl)) {
        channel.listen('.financial.data.changed', () => {
            window.location.reload();
        });
    }

    if (datasetEnabled(profileEl)) {
        channel.listen('.financial.data.changed', () => {
            window.location.reload();
        });
    }

    if (datasetEnabled(treeEl)) {
        channel.listen('.tree.updated', () => {
            window.location.reload();
        });
    }
}
