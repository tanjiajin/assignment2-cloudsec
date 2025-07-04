function addOrderItem() {
    const container = document.getElementById("order-items");
    const div = document.createElement("div");
    div.className = "order-row";
    div.innerHTML = `
        <label>Select an item:</label>
        <select name="item[]">
            <option value="TIRA MISS U">TIRA MISS U</option>
            <option value="CHOC CAKE">MOIST CHOCOLATE CAKE</option>
            <option value="CHEESECAKE">CHEESECAKE</option>
            <option value="RED VELVET">RED VELVET CAKE</option>
            <option value="SALTED EGG CROISSANT">SALTED EGG CROISSANT</option>
            <option value="PEACH STRUDLE">PEACH STRUDLE</option>
            <option value="PUFF">VHICKEN CURRY PUFF</option>
            <option value="PIE">CHERRY PIE</option>
            <option value="ICE CREAM">ICE CREAM PADDLE POP</option>
            <option value="MATCHA LATTE">MATCHA LATTE</option>
            <option value="ICED CHOCOLATE">ICED CHOCOLATE</option>
            <option value="HOT COFFEE">HOT COFFEE</option>
            <option value="CHEESE BAGEL">CHEESE BAGEL</option>
            <option value="PASTA PESTO">PASTA PESTO</option>
            <option value="MAC N CHEESE">MAC N CHEESE</option>
            <option value="LASAGNA">LASAGNA</option>
        </select>
        <input type="number" name="quantity[]" min="0" placeholder="0" required>
        <input type="text" name="remark[]" placeholder="remark">
        <button type="button" onclick="this.parentElement.remove()">Remove</button>
        <br><br>
    `;
    container.appendChild(div);
}

function validateOrder() {
    const items = document.getElementsByName("item[]");
    const quantities = document.getElementsByName("quantity[]");
    const remarks = document.getElementsByName("remark[]");
    const receiptList = document.getElementById("receipt-list");
    let receiptVisible = false;

    receiptList.innerHTML = ""; // Clear previous receipt

    for (let i = 0; i < quantities.length; i++) {
        const quantity = parseInt(quantities[i].value);
        if (quantity > 0) {
            const item = items[i].value;
            const li = document.createElement("li");
            li.textContent = `${item} x ${quantity} (${remarks[i].value})`; ;
            receiptList.appendChild(li);
            receiptVisible = true;
        }
    }

    if (receiptVisible) {
        document.getElementById("receipt").style.display = "block";
        return false; // Prevent actual form submission
    } else {
        alert("Please make an order.");
        return false;
    }
}

window.onload = function () {
    addOrderItem();
};
