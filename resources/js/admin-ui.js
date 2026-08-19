window.AdminUI = {
    escapeHtml(value) {
        if (value === null || value === undefined) return '';

        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", "&#039;");
    },

    extractError(error) {
        if (error?.data?.errors) {
            const errors = Object.values(error.data.errors).flat();

            if (errors.length) return errors.join(' ');
        }

        return error?.data?.message ||
            error?.message ||
            'Something went wrong.';
    },

    debounce(callback, delay = 350) {
        let timer;

        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => callback(...args), delay);
        };
    },

    query(params = {}) {
        const query = new URLSearchParams();

        Object.entries(params).forEach(([key, value]) => {
            if (
                value === null ||
                value === undefined ||
                value === ''
            ) return;

            query.set(key, value);
        });

        return query.toString();
    },

    formData(data = {}) {
        const form = new FormData();

        Object.entries(data).forEach(([key, value]) => {
            if (
                value === null ||
                value === undefined ||
                value === ''
            ) return;

            if (Array.isArray(value)) {
                value.forEach(item => {
                    form.append(`${key}[]`, item);
                });

                return;
            }

            if (typeof value === 'boolean') {
                form.append(key, value ? '1' : '0');
                return;
            }

            form.append(key, value);
        });

        return form;
    },

    openModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    },

    closeModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if (!document.querySelector('.app-modal-overlay.flex')) {
            document.body.classList.remove('overflow-hidden');
        }
    },

    closeAllModals() {
        document.querySelectorAll('.app-modal-overlay').forEach(modal => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        document.body.classList.remove('overflow-hidden');
    },

    setLoading(button, text = 'Processing...') {
        if (typeof button === 'string') {
            button = document.getElementById(button);
        }

        if (!button) return;

        if (!button.dataset.originalHtml) {
            button.dataset.originalHtml = button.innerHTML;
        }

        button.disabled = true;
        button.innerHTML = text;
    },

    resetLoading(button) {
        if (typeof button === 'string') {
            button = document.getElementById(button);
        }

        if (!button) return;

        button.disabled = false;

        if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
        }
    },

    showError(id, message) {
        const box = document.getElementById(id);

        if (!box) return;

        box.textContent = message;
        box.classList.remove('hidden');
    },

    clearError(id) {
        const box = document.getElementById(id);

        if (!box) return;

        box.textContent = '';
        box.classList.add('hidden');
    },

    formatDate(value, withTime = false) {
        if (!value) return 'N/A';

        const options = {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        };

        if (withTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
        }

        return new Date(value).toLocaleString('en-GB', options);
    },

    formatBytes(bytes) {
        const value = Number(bytes ?? 0);

        if (value < 1024) return `${value} B`;
        if (value < 1048576) return `${(value / 1024).toFixed(1)} KB`;
        if (value < 1073741824) return `${(value / 1048576).toFixed(1)} MB`;

        return `${(value / 1073741824).toFixed(1)} GB`;
    },

    formatNumber(value, decimals = 0) {
        return Number(value ?? 0).toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    },

    statusBadge(status) {
        const map = {
            active: 'bg-emerald-50 text-emerald-700',
            approved: 'bg-emerald-50 text-emerald-700',
            completed: 'bg-emerald-50 text-emerald-700',
            sent: 'bg-emerald-50 text-emerald-700',

            pending: 'bg-amber-50 text-amber-700',
            processing: 'bg-amber-50 text-amber-700',
            sending: 'bg-amber-50 text-amber-700',

            rejected: 'bg-red-50 text-red-700',
            suspended: 'bg-red-50 text-red-700',
            failed: 'bg-red-50 text-red-700',
            urgent: 'bg-red-50 text-red-700',

            inactive: 'bg-slate-100 text-slate-600',
            cancelled: 'bg-slate-100 text-slate-600',
            draft: 'bg-slate-100 text-slate-600'
        };

        const value = String(status ?? 'unknown');

        return `
            <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold ${map[value] ?? 'bg-slate-100 text-slate-600'}">
                ${this.escapeHtml(this.titleCase(value))}
            </span>
        `;
    },

    priorityBadge(priority) {
        const map = {
            low: 'bg-sky-50 text-sky-700',
            normal: 'bg-slate-100 text-slate-600',
            high: 'bg-amber-50 text-amber-700',
            urgent: 'bg-red-50 text-red-700'
        };

        const value = String(priority ?? 'normal');

        return `
            <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold ${map[value] ?? map.normal}">
                ${this.escapeHtml(this.titleCase(value))}
            </span>
        `;
    },

    visibilityBadge(value) {
        const map = {
            public: 'bg-emerald-50 text-emerald-700',
            members: 'bg-indigo-50 text-indigo-700',
            internal: 'bg-amber-50 text-amber-700',
            private: 'bg-red-50 text-red-700'
        };

        const visibility = String(value ?? 'internal');

        return `
            <span class="rounded-full px-2 py-1 text-[9px] font-semibold ${map[visibility] ?? map.internal}">
                ${this.escapeHtml(this.titleCase(visibility))}
            </span>
        `;
    },

    titleCase(value) {
        return String(value ?? '')
            .replaceAll('_', ' ')
            .replace(/\b\w/g, char => char.toUpperCase());
    },

    emptyState(message = 'No data found.', colspan = null) {
        if (colspan) {
            return `
                <tr>
                    <td colspan="${colspan}" class="px-6 py-10 text-center text-sm text-slate-400">
                        ${this.escapeHtml(message)}
                    </td>
                </tr>
            `;
        }

        return `
            <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-sm text-slate-400">
                ${this.escapeHtml(message)}
            </div>
        `;
    },

    loadingState(message = 'Loading...', colspan = null) {
        if (colspan) {
            return `
                <tr>
                    <td colspan="${colspan}" class="px-6 py-10 text-center text-sm text-slate-400">
                        ${this.escapeHtml(message)}
                    </td>
                </tr>
            `;
        }

        return `
            <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-sm text-slate-400">
                ${this.escapeHtml(message)}
            </div>
        `;
    },

    renderPagination({
        container,
        currentPage = 1,
        lastPage = 1,
        total = null,
        onPageChange
    }) {
        const el = typeof container === 'string'
            ? document.getElementById(container)
            : container;

        if (!el) return;

        if (lastPage <= 1) {
            el.innerHTML = '';
            return;
        }

        el.innerHTML = `
            <div class="flex items-center justify-between gap-3">
                <span class="text-xs text-slate-500">
                    Page ${currentPage} of ${lastPage}
                    ${total !== null ? ` • ${total} records` : ''}
                </span>

                <div class="flex gap-2">
                    <button
                        type="button"
                        ${currentPage <= 1 ? 'disabled' : ''}
                        data-page="${currentPage - 1}"
                        class="admin-page-btn rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                        Previous
                    </button>

                    <button
                        type="button"
                        ${currentPage >= lastPage ? 'disabled' : ''}
                        data-page="${currentPage + 1}"
                        class="admin-page-btn rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                        Next
                    </button>
                </div>
            </div>
        `;

        el.querySelectorAll('.admin-page-btn').forEach(button => {
            button.addEventListener('click', () => {
                const page = Number(button.dataset.page);

                if (
                    page >= 1 &&
                    page <= lastPage &&
                    typeof onPageChange === 'function'
                ) {
                    onPageChange(page);
                }
            });
        });
    },

    async confirm(message = 'Are you sure?') {
        return window.confirm(message);
    },

    async copy(value) {
        await navigator.clipboard.writeText(String(value ?? ''));
    },

    resetForm(id) {
        const form = typeof id === 'string'
            ? document.getElementById(id)
            : id;

        if (form?.reset) form.reset();
    },

    async deleteRequest(url, {
        message = 'Delete this item?',
        successMessage = 'Deleted successfully.',
        onSuccess = null
    } = {}) {
        if (!await this.confirm(message)) return false;

        try {
            await api(url, { method: 'DELETE' });

            if (window.Toast && successMessage) {
                Toast.success(successMessage);
            }

            if (typeof onSuccess === 'function') {
                await onSuccess();
            }

            return true;
        } catch (error) {
            if (window.Toast) {
                Toast.error(this.extractError(error));
            } else {
                console.error(error);
            }

            return false;
        }
    },

    async request(url, {
        method = 'POST',
        data = {},
        confirmMessage = null,
        successMessage = null,
        onSuccess = null
    } = {}) {
        if (confirmMessage && !await this.confirm(confirmMessage)) {
            return null;
        }

        try {
            const options = { method };

            if (data !== null && data !== undefined) {
                options.body = data instanceof FormData
                    ? data
                    : JSON.stringify(data);
            }

            const response = await api(url, options);

            if (window.Toast && successMessage) {
                Toast.success(successMessage);
            }

            if (typeof onSuccess === 'function') {
                await onSuccess(response);
            }

            return response;
        } catch (error) {
            if (window.Toast) {
                Toast.error(this.extractError(error));
            } else {
                console.error(error);
            }

            return null;
        }
    }
};

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        AdminUI.closeAllModals();
    }
});

document.addEventListener('click', event => {
    const overlay = event.target.closest('.app-modal-overlay');

    if (
        overlay &&
        event.target === overlay
    ) {
        AdminUI.closeModal(overlay.id);
    }
});