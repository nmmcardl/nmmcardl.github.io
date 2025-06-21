function calculateTotal(quantity, price) {
  return quantity * price;
}

function outputCartRow(item, total) {
  document.write('<tr>');
  document.write(`<td><img src="images/${item.product.filename}"></td>`);
  document.write(`<td>${item.product.title}</td>`);
  document.write(`<td>${item.quantity}</td>`);
  document.write(`<td>$${item.product.price.toFixed(2)}</td>`);
  document.write(`<td>$${total.toFixed(2)}</td>`);
  document.write('</tr>');
}