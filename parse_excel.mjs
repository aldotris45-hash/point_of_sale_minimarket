import fs from 'fs';
import path from 'path';
import xlsx from 'xlsx';

const dirPath = 'D:\\BAKOL SAYUR';
const files = ['Invoice Februari.xlsx', 'Invoice januari - ledokombo.xlsx', 'Invoice januari.xlsx'];

const excelDateToJSDate = (serial) => {
    if (!serial || isNaN(serial)) return null;
    const utc_days  = Math.floor(serial - 25569);
    const utc_value = utc_days * 86400;                                        
    const date_info = new Date(utc_value * 1000);
    return date_info.toISOString().split('T')[0];
};

const results = [];
let lastCustomer = 'Pelanggan Umum';

files.forEach(file => {
    const fullPath = path.join(dirPath, file);
    if (!fs.existsSync(fullPath)) return;
    
    console.log(`Processing ${file}...`);
    const workbook = xlsx.readFile(fullPath);
    
    workbook.SheetNames.forEach(sheetName => {
        const worksheet = workbook.Sheets[sheetName];
        const data = xlsx.utils.sheet_to_json(worksheet, { header: 1 });
        
        try {
            let customer = '';
            let invoiceNo = '';
            let date = '';
            let items = [];
            
            // Extract Headers
            for (let i = 0; i < Math.min(15, data.length); i++) {
                const row = data[i];
                if (!row) continue;
                
                if (row.includes('Bill To:')) {
                    const custRow = data[i + 1] || [];
                    customer = custRow.find(val => val && typeof val === 'string' && val !== '' && !val.includes('INVOICE') && !val.includes('MODAL'));
                }
                
                if (row.includes('Invoice #:')) {
                    const idx = row.indexOf('Invoice #:') + 1;
                    invoiceNo = row[idx] || '';
                }
                
                if (row.includes('Invoice Date:')) {
                     const idx = row.indexOf('Invoice Date:') + 1;
                     date = excelDateToJSDate(row[idx]) || row[idx];
                }
            }
            
            if (customer && customer.toLowerCase().includes('name/company')) {
                customer = lastCustomer; // fallback to previous or known adjacent
            } else if (customer) {
                lastCustomer = customer;
            } else {
                customer = lastCustomer;
            }
            
            // Extract Items
            let inItemSection = false;
            let itemHeaderOffset = 0;
            
            for (let i = 0; i < data.length; i++) {
                const row = data[i];
                if (!row) continue;
                
                if (row.includes('Item Name') && row.includes('Price')) {
                    inItemSection = true;
                    // Find actual column indexes
                    itemHeaderOffset = row.indexOf('Item Name');
                    continue;
                }
                
                if (inItemSection) {
                    if (row.includes('Subtotal:') || row.includes('Tax:') || row.includes('Amount Due:')) {
                        break;
                    }
                    
                    const itemName = row[1] || row[itemHeaderOffset];
                    const unit = row[2];
                    const sellPrice = row[3];
                    const qty = row[4];
                    const sellTotal = row[5];
                    
                    // Modal parsing (Right side)
                    let modalName = row[9];
                    let modalPrice = row[11]; // Usually column L
                    let modalTotal = row[13]; // Usually column N
                    
                    // User said: "meskipun ga ada namanya, samakan sebelah"
                    if (!modalPrice && row[10] && typeof row[10] === 'number') {
                        modalPrice = row[10]; // fallback if columns shifted
                    }
                    if (!modalPrice) modalPrice = 0;
                    
                    if (itemName && sellPrice && qty && String(itemName).trim() !== '') {
                        items.push({
                            name: String(itemName).trim(),
                            unit: String(unit || 'Kilo'),
                            sell_price: parseFloat(sellPrice),
                            cost_price: parseFloat(modalPrice), // MODAL PRICE!
                            qty: parseFloat(qty),
                            subtotal: parseFloat(sellTotal)
                        });
                    }
                }
            }
            
            if (items.length > 0) {
                // Deduplication check
                const isDupe = results.find(r => r.date === date && r.invoiceNo === invoiceNo && r.totalSales === items.reduce((sum, item) => sum + item.subtotal, 0));
                
                if (!isDupe) {
                    results.push({
                        file,
                        sheet: sheetName,
                        customer: customer.trim(),
                        invoiceNo: invoiceNo,
                        date: date,
                        totalItems: items.length,
                        totalCost: items.reduce((sum, item) => sum + (item.cost_price * item.qty), 0),
                        totalSales: items.reduce((sum, item) => sum + item.subtotal, 0),
                        profit: items.reduce((sum, item) => sum + (item.subtotal - (item.cost_price * item.qty)), 0),
                        items: items
                    });
                }
            }
        } catch (e) {
            console.error(`Error parsing ${file} -> ${sheetName}:`, e.message);
        }
    });
});

fs.writeFileSync('D:\\BAKOL SAYUR\\parsed_data.json', JSON.stringify(results, null, 2));

console.log("\n=== SUMMARY BARU (DENGAN HARGA MODAL) ===");
console.log(`Total Invoices Unik Found: ${results.length}`);
console.log("-------------------------------------------------------------------------");
results.forEach(r => {
    console.log(`[${r.date}] ${r.customer.substring(0,20).padEnd(20)} | Inv: ${String(r.invoiceNo).padEnd(14)} | Sales: Rp ${r.totalSales.toLocaleString('id-ID')} | Modal: Rp ${r.totalCost.toLocaleString('id-ID')} | Profit: Rp ${r.profit.toLocaleString('id-ID')}`);
});
