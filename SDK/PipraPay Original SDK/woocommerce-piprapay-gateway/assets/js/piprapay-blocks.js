const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { createElement } = window.wp.element;

const paymentData = window.wc.wcSettings.getSetting('paymentMethodData', {})['piprapay'] || {};

const label = createElement(
    'span',
    null,
    paymentData.icon && createElement('img', { src: paymentData.icon, alt: paymentData.title, style: { verticalAlign: 'middle', marginRight: '8px' } }),
    paymentData.title
);

const content = createElement('div', null, paymentData.description);
const edit = createElement('div', null, paymentData.description);

registerPaymentMethod({
    name: 'piprapay',
    label: label,
    content: content,
    edit: edit,
    canMakePayment: () => true,
    ariaLabel: paymentData.title,
    supports: ['products'],
});