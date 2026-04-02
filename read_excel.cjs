const xlsx = require('xlsx');

const workbook = xlsx.readFile('D:\\BAKOL SAYUR\\Invoice Februari.xlsx');
workbook.SheetNames.forEach(sheetName => {
    console.log('\n--- Sheet:', sheetName, '---');
    const worksheet = workbook.Sheets[sheetName];
    const data = xlsx.utils.sheet_to_json(worksheet, { header: 1 });
    data.forEach((row, i) => {
        if (row.length > 0) {
            console.log(`Row ${i}:`, row);
        }
    });
});
