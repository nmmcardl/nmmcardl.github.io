const tax_rate = parseFloat(prompt('Enter tax rate (e.g. 0.10 for 10%)'));
const shipping_threshold = parseFloat(prompt('Enter shipping threshold (e.g. 1000)'));

let subtotal = 0;

for (let i = 0; i < cart.length; i++) {
  const item = cart[i];
  const total = calculateTotal(item.quantity, item.product.price);
  subtotal += total;
  outputCartRow(item, total);
}

const tax = subtotal * tax_rate;
const shipping = (subtotal > shipping_threshold) ? 0 : 40;
const grandTotal = subtotal + tax + shipping;

document.write(`<tr class="totals"><td colspan="4">Subtotal</td><td>$${subtotal.toFixed(2)}</td></tr>`);
document.write(`<tr class="totals"><td colspan="4">Tax</td><td>$${tax.toFixed(2)}</td></tr>`);
document.write(`<tr class="totals"><td colspan="4">Shipping</td><td>$${shipping.toFixed(2)}</td></tr>`);
document.write(`<tr class="totals"><td colspan="4" class="focus">Grand Total</td><td class="focus">$${grandTotal.toFixed(2)}</td></tr>`);