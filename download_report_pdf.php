<?php
require 'vendor/autoload.php'; // Dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

include 'db.php';

// Fetch logs
$logs = $conn->query("
    SELECT pu.*, p.Product_Name, s.Staff_Name 
    FROM product_updates pu
    JOIN products p ON pu.Product_ID = p.Product_ID
    JOIN staff s ON pu.Staff_ID = s.Staff_ID
    ORDER BY pu.Update_Time DESC
");

ob_start(); // Start output buffering
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #999;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color:rgb(241, 247, 161);
        }
        .positive { color: green; }
    </style>
</head>
<body>
    <h2>Product Stock Update Report</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Updated By</th>
                <th>Previous Stock</th>
                <th>New Stock</th>
                <th>Difference</th>
                <th>Notes</th>
                <th>Update Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs->num_rows > 0): ?>
                <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['Product_Name']) ?></td>
                        <td><?= htmlspecialchars($log['Staff_Name']) ?></td>
                        <td><?= $log['Previous_Stock'] ?></td>
                        <td><?= $log['New_Stock'] ?></td>
                        <td class="positive">+<?= $log['Difference'] ?></td>
                        <td><?= htmlspecialchars($log['Notes']) ?></td>
                        <td><?= date("M j, Y g:i A", strtotime($log['Update_Time'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No product update records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$html = ob_get_clean();

// Setup Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("stock_update_report.pdf", ["Attachment" => true]);
exit;
