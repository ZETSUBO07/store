/**
 * Custom JavaScript for Stationery Sales System
 */

document.addEventListener('DOMContentLoaded', function() {
    // ฟังก์ชั่นคำนวณยอดรวมสินค้าในการขาย
    const calculateTotal = function() {
        let total = 0;
        const qtyInputs = document.querySelectorAll('.product-qty');
        const priceInputs = document.querySelectorAll('.product-price');
        const amountInputs = document.querySelectorAll('.product-amount');
        
        for (let i = 0; i < qtyInputs.length; i++) {
            if (qtyInputs[i].value && priceInputs[i].value) {
                const qty = parseFloat(qtyInputs[i].value);
                const price = parseFloat(priceInputs[i].value);
                const amount = qty * price;
                
                if (amountInputs[i]) {
                    amountInputs[i].value = amount.toFixed(2);
                }
                
                total += amount;
            }
        }
        
        // แสดงยอดรวม
        const totalElement = document.getElementById('sale-total');
        if (totalElement) {
            totalElement.textContent = total.toFixed(2);
        }
        
        // ใส่ค่าในฟิลด์ input สำหรับส่งฟอร์ม
        const totalInput = document.getElementById('sale-total-input');
        if (totalInput) {
            totalInput.value = total.toFixed(2);
        }
    };
    
    // เพิ่ม Event Listener สำหรับการเปลี่ยนแปลงจำนวนสินค้า
    const qtyInputs = document.querySelectorAll('.product-qty');
    qtyInputs.forEach(function(input) {
        input.addEventListener('change', calculateTotal);
        input.addEventListener('keyup', calculateTotal);
    });
    
    // เพิ่ม Event Listener สำหรับการเปลี่ยนแปลงราคาสินค้า
    const priceInputs = document.querySelectorAll('.product-price');
    priceInputs.forEach(function(input) {
        input.addEventListener('change', calculateTotal);
        input.addEventListener('keyup', calculateTotal);
    });
    
    // เพิ่ม Event Listener สำหรับปุ่มลบรายการสินค้า
    const removeButtons = document.querySelectorAll('.btn-remove-item');
    removeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            if (row) {
                row.remove();
                calculateTotal();
            }
        });
    });
    
    // เพิ่ม Event Listener สำหรับการเลือกสินค้า
    const productSelect = document.getElementById('product-select');
    if (productSelect) {
        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const priceInput = document.getElementById('product-price');
            
            if (priceInput && selectedOption.value) {
                priceInput.value = selectedOption.getAttribute('data-price');
            }
        });
    }
    
    // เพิ่ม Event Listener สำหรับการค้นหา
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                document.getElementById('search-form').submit();
            }
        });
    }
    
    // เพิ่ม Event Listener สำหรับปุ่มพิมพ์ใบเสร็จ
    const printReceiptBtn = document.getElementById('print-receipt-btn');
    if (printReceiptBtn) {
        printReceiptBtn.addEventListener('click', function() {
            window.print();
        });
    }
    
    // เพิ่ม Event Listener สำหรับปุ่มเพิ่มรายการสินค้า
    const addItemBtn = document.getElementById('add-item-btn');
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            const productSelect = document.getElementById('product-select');
            const qtyInput = document.getElementById('product-qty');
            
            if (!productSelect.value) {
                alert('กรุณาเลือกสินค้า');
                return;
            }
            
            if (!qtyInput.value || parseInt(qtyInput.value) <= 0) {
                alert('กรุณาระบุจำนวนสินค้าให้ถูกต้อง');
                return;
            }
            
            // สร้างแถวใหม่ในตาราง
            const tableBody = document.getElementById('sale-items-table').getElementsByTagName('tbody')[0];
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const productId = productSelect.value;
            const productName = selectedOption.text;
            const price = selectedOption.getAttribute('data-price');
            const qty = parseInt(qtyInput.value);
            const amount = (price * qty).toFixed(2);
            
            // ตรวจสอบว่ามีสินค้านี้ในตารางแล้วหรือไม่
            const rows = tableBody.getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (cells[0].querySelector('input').value === productId) {
                    alert('สินค้านี้มีอยู่ในรายการแล้ว กรุณาแก้ไขจำนวนแทน');
                    return;
                }
            }
            
            // สร้างแถวใหม่
            const newRow = tableBody.insertRow();
            newRow.innerHTML = `
                <td>
                    <input type="hidden" name="product_id[]" value="${productId}">
                    ${productName}
                </td>
                <td class="text-center">${qty} ${selectedOption.getAttribute('data-unit')}</td>
                <td class="text-end">${parseFloat(price).toFixed(2)} บาท</td>
                <td class="text-end">${amount} บาท</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            // เพิ่ม Event Listener สำหรับปุ่มลบ
            newRow.querySelector('.btn-remove-item').addEventListener('click', function() {
                newRow.remove();
                calculateTotal();
            });
            
            // คำนวณยอดรวมใหม่
            calculateTotal();
            
            // รีเซ็ตฟอร์ม
            productSelect.selectedIndex = 0;
            qtyInput.value = 1;
            priceInput.value = '';
        });
    }
});
