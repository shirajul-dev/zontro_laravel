//all declaration#
let chartTransactionStatistics = null;
let chartGatewayStatistics = null;
window.InvoiceCustomerChoices = null;

(function () {
    const choicesInstances = new Map();

    window.initChoices = function (selector = '.js-select') {
        document.querySelectorAll(selector).forEach(select => {

            // Prevent double init
            if (choicesInstances.has(select)) return;

            const isMultiple = select.hasAttribute('multiple');

            const instance = new Choices(select, {
                removeItemButton: select.dataset.remove === 'true' && isMultiple,
                searchEnabled: select.dataset.search !== 'false',
                shouldSort: false,
                placeholder: true,
                placeholderValue: select.dataset.placeholder || 'Select option',
                searchPlaceholderValue: 'Search...',
                allowHTML: false,
            });

            choicesInstances.set(select, instance);
        });
    };

    document.addEventListener('DOMContentLoaded', () => initChoices());
})();

function initInvoiceCustomer() {
    const el = document.querySelector('.customersList');
    if (!el) return;

    // ✅ already initialized? then STOP
    if (el.dataset.choicesInitialized === '1') return;

    window.InvoiceCustomerChoices = new Choices(el, {
        removeItemButton: true,
        searchEnabled: true,
        shouldSort: false,
    });

    el.dataset.choicesInitialized = '1';
}

function initTags() {
    const tagInputs = document.querySelectorAll('.js-tags');

    tagInputs.forEach(input => {

        // ✅ Prevent duplicate initialization
        if (input.dataset.tagsInitialized === "1") return;
        input.dataset.tagsInitialized = "1";

        let tags = [];

        // Read existing value
        if (input.value.trim() !== '') {
            tags = input.value.split(',').map(t => t.trim()).filter(Boolean);
        }

        // Create container
        const container = document.createElement('div');
        container.className = 'tag-container d-flex flex-wrap gap-2';
        input.parentNode.insertBefore(container, input);
        container.appendChild(input);

        // Create hidden input ONLY ONCE
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = input.id; // or input.name
        container.appendChild(hidden);

        input.removeAttribute('name');
        input.value = '';

        renderTags();

        // Add tag on Enter
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                const value = input.value.trim();
                if (!value || tags.includes(value)) return;

                tags.push(value);
                input.value = '';
                renderTags();
            }
        });

        function renderTags() {
            container.querySelectorAll('.tag-item').forEach(tag => tag.remove());

            tags.forEach((tag, index) => {
                const tagEl = document.createElement('span');
                tagEl.className = 'badge bg-primary tag-item text-white d-flex align-items-center';
                tagEl.style.fontWeight = '400';
                tagEl.innerHTML = `
                            ${tag}
                            <span class="ms-2 cursor-pointer" data-index="${index}">×</span>
                        `;
                container.insertBefore(tagEl, input);
            });

            hidden.value = tags.join(',');
        }

        // Remove tag
        container.addEventListener('click', function (e) {
            if (e.target.dataset.index !== undefined) {
                tags.splice(e.target.dataset.index, 1);
                renderTags();
            }
        });
    });
}

var myModalElTWOSTEPVERIFY = document.getElementById('model-my-action-confirmation');

myModalElTWOSTEPVERIFY.addEventListener('hidden.bs.modal', function () {
    document.querySelector("#my-action-confirmation-btn").value = '';
});

function show_action_confirmation_tab(btnClass, title, btnTitle, btnColor) {
    var myModalEl = document.getElementById('model-my-action-confirmation');

    closeAllBootstrapModals();

    document.querySelector(".model-my-action-confirmation-btn-title").innerHTML = title;
    document.querySelector("#model-my-action-confirmation-btn").innerHTML = btnTitle;

    const btnClasss = document.getElementById('model-my-action-confirmation-btn');

    const keepClasses = ['btn', 'btn-sm'];

    btnClasss.classList.forEach(cls => {
        if (!keepClasses.includes(cls)) {
            btnClasss.classList.remove(cls);
        }
    });

    document.querySelector("#model-my-action-confirmation-btn").classList.add(btnColor);

    var button = document.getElementById('model-my-action-confirmation-btn');

    document.querySelector("#my-action-confirmation-btn").value = '.' + btnClass;

    $('#model-my-action-confirmation').modal('show');
}

function my_action_confirmation_btn() {
    var btnClass = document.querySelector("#my-action-confirmation-btn").value;

    document.querySelector(btnClass).click();
    document.querySelector("#my-action-confirmation-btn").value = '';
}

var myModalElTWOSTEPVERIFY = document.getElementById('model-my-two-step-verify');

myModalElTWOSTEPVERIFY.addEventListener('hidden.bs.modal', function () {
    document.querySelector("#my-two-step-verify-code").value = '';
});

function copyContent(content, title, description) {
    if (!content) {
        // Show error if URL is empty
        createToast({
            title: 'Error!',
            description: 'No content provided to copy.',
            svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
            timeout: 6000,
            top: 70
        });
        return;
    }

    // Use the Clipboard API
    navigator.clipboard.writeText(content).then(() => {
        // Success toast
        createToast({
            title: title,
            description: description,
            svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5f38f9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-circle-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>`,
            timeout: 4000,
            top: 70
        });
    }).catch((err) => {
        // Error toast
        createToast({
            title: 'Failed!',
            description: 'Unable to copy the content. Please try manually.',
            svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
            timeout: 6000,
            top: 70
        });
        console.error('Clipboard error:', err);
    });
}

function show_two_step_verify_tab(btnClass) {
    var myModalEl = document.getElementById('model-my-two-step-verify');

    if (myModalEl && myModalEl.classList.contains('show')) {
        var my_two_step_verify_code = document.querySelector("#my-two-step-verify-code").value;

        if (my_two_step_verify_code == "") {
            document.querySelector("#my-two-step-verify-code").reportValidity();
        }
    } else {
        closeAllBootstrapModals();

        var button = document.getElementById('model-my-two-step-verify-btn');

        document.querySelector("#my-two-step-verify-btn").value = '.' + btnClass;
        document.querySelector("#my-two-step-verify-code").value = '';

        $('#model-my-two-step-verify').modal('show');
    }
}

function closeAllBootstrapModals() {
    $('.modal.show').each(function () {
        $(this).modal('hide');
    });
}

function two_step_verify_tab_btn() {
    var btnClass = document.querySelector("#my-two-step-verify-btn").value;

    document.querySelector(btnClass).click();
}

function isMobileDevice() {
    return window.innerWidth <= 768;
}

function filter_hide_show(tab) {
    var element = document.querySelector('.' + tab);

    if (element.classList.contains('d-none')) {
        element.classList.remove('d-none');
    } else {
        element.classList.add('d-none');
    }
}

function showProgress() {
    const progress = document.getElementById('topProgress');
    const bar = progress.querySelector('.progress-bar');

    progress.classList.remove('d-none');
    bar.style.width = '30%';

    setTimeout(() => bar.style.width = '60%', 200);
    setTimeout(() => bar.style.width = '85%', 400);
}

function hideProgress() {
    const progress = document.getElementById('topProgress');
    const bar = progress.querySelector('.progress-bar');

    bar.style.width = '100%';

    setTimeout(() => {
        progress.classList.add('d-none');
        bar.style.width = '0%';
    }, 300);
}

function set_brand(brand_id) {
    var csrf_token_default = $('input[name="csrf_token_default"]').val();

    if (isMobileDevice()) {
        const sidebar = document.getElementById('sidebarMenu');

        if (sidebar && sidebar.classList.contains('offcanvas-md') && sidebar.classList.contains('offcanvas-start') && sidebar.classList.contains('sidebar') && sidebar.classList.contains('show')) {
            const toggleBtn = document.querySelector('.navbar-toggler');
            if (toggleBtn) toggleBtn.click();
        }
    }

    showProgress();

    $.ajax({
        type: 'POST',
        url: '<?php echo $site_url.$path_admin ?>/dashboard',
        data: { action: "set-default-brand", brand_id: brand_id, csrf_token: csrf_token_default },
        dataType: 'json',
        success: function (response) {
            $('input[name="csrf_token_default"]').val(response.csrf_token);

            document.querySelectorAll('input[name="csrf_token"]').forEach(input => {
                input.value = response.csrf_token;
            });

            if (response.status === 'true') {
                location.reload();
            } else {
                hideProgress();

                createToast({
                    title: response.title,
                    description: response.message,
                    svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
                    timeout: 6000,
                    top: 70
                });
            }
        },
        error: function (xhr, status, error) {
            hideProgress();

            createToast({
                title: 'Something Wrong!',
                description: 'For further assistance, please contact our support team.',
                svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
                timeout: 6000,
                top: 70
            });
        }
    });
}

function initHugeRTE(selector = '.hugerte-textArea') {
    // Get all textarea elements with the class
    const elements = document.querySelectorAll(selector);

    elements.forEach(el => {
        // Avoid initializing twice
        if (!el.dataset.hugerteInitialized) {
            let options = {
                target: el, // Initialize directly on the element
                height: 250,
                menubar: false,
                statusbar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
                    'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; -webkit-font-smoothing: antialiased; }'
            };

            // Dark mode support
            if (localStorage.getItem("tablerTheme") === 'dark') {
                options.skin = 'oxide-dark';
                options.content_css = 'dark';
            }

            hugeRTE.init(options);

            // Mark as initialized
            el.dataset.hugerteInitialized = 'true';
        }
    });
}

// Run it on all .hugerte-textArea textareas
initHugeRTE();

function getAdminPath(url) {
    let cleanUrl = url.split('?')[0];
    let index = cleanUrl.indexOf('<?php echo $path_admin?>/');
    if (index === -1) return '';

    return cleanUrl.substring(index + '<?php echo $path_admin?>/'.length).replace(/^\/+/, '');
}

function getQueryParams(url) {
    const params = {};
    const queryString = url.split('?')[1];
    if (!queryString) return params;

    const searchParams = new URLSearchParams(queryString);
    for (const [key, value] of searchParams.entries()) {
        params[key] = value === '' ? true : value;
    }
    return params;
}

function initToolTips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        const existing = tabler.bootstrap.Tooltip.getInstance(el);
        if (existing) {
            existing.dispose();
        }

        const config = {
            html: el.dataset.bsHtml === 'true',
            placement: el.dataset.bsPlacement || 'top',
            trigger: el.dataset.bsTrigger || 'hover focus',
            container: 'body',
            delay: {
                show: 100,
                hide: 100
            }
        };

        if (el.dataset.bsDelay) {
            const delay = parseInt(el.dataset.bsDelay, 10);
            if (!isNaN(delay)) {
                config.delay = { show: delay, hide: delay };
            }
        }

        new tabler.bootstrap.Tooltip(el, config);
    });
}

function updateCsrfTokens(token) {
    if (!token) return;
    $('input[name="csrf_token"]').val(token);
    $('input[name="csrf_token_default"]').val(token);
}

function load_content(page, url, nav_id, fromPopState = false) {
    const requestUrl = new URL(url, window.location.origin);
    requestUrl.searchParams.set('content', '1');

    showProgress();

    if (isMobileDevice()) {
        const sidebar = document.getElementById('sidebarMenu');
        if (sidebar && sidebar.classList.contains('show')) {
            const toggleBtn = document.querySelector('.navbar-toggler');
            if (toggleBtn) toggleBtn.click();
        }
    }

    $.ajax({
        url: requestUrl.toString(),
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function (html) {
            $('.root-print').html(html);

            // Re-init components safely
            if (typeof initHugeRTE === 'function') initHugeRTE();
            if (typeof initInvoiceCustomer === 'function') initInvoiceCustomer();
            if (typeof initToolTips === 'function') initToolTips();
            if (typeof initChoices === 'function') {
                initChoices();
                initChoices('.js-select');
            }
            if (typeof initTags === 'function') initTags();

            // Safe re-init of dropdowns using getOrCreateInstance
            if (typeof tabler !== 'undefined' && tabler.bootstrap) {
                document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => {
                    tabler.bootstrap.Dropdown.getOrCreateInstance(el);
                });
            }

            hideProgress();

            if (!fromPopState) {
                history.pushState({ page: page, path: url, nav_id: nav_id }, "", url);
            }
        },
        error: function (xhr, status, error) {
            hideProgress();
            console.error('AJAX Load Error:', error);
        }
    });

    document.querySelectorAll('#sidebarMenu .nav-link').forEach(link => {
        link.classList.remove('active');
    });
    const activeLink = document.querySelector('#sidebarMenu .' + nav_id + ' .nav-link');
    if (activeLink) {
        activeLink.classList.add('active');
    }

    document.title = page + ' - ZontroPay';
}

window.addEventListener("popstate", function (event) {
    if (event.state) {
        load_content(event.state.page, event.state.path, event.state.nav_id, true);
    }
});

document.addEventListener("DOMContentLoaded", function () {
    let currentUrlV = window.location.href;

    if (currentUrlV == '<?php echo $site_url.$path_admin ?>/') {
        var currentUrl = '<?php echo $site_url.$path_admin ?>/dashboard';
    } else {
        var currentUrl = window.location.href;
    }

    const cleanPath = getAdminPath(currentUrl);

    let pageTitle = cleanPath.split('/').map(segment => segment.replace(/-/g, ' ').replace(/\b\w/g, char => char.toUpperCase())).join(' - ') || 'Dashboard';

    let nav_id = 'nav-item-' + (cleanPath.split('/')[0] || 'dashboard');

    load_content(pageTitle, currentUrl, nav_id);
});
