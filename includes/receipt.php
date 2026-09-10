<?php
/**
 * Build the HTML for a payment receipt. Used both for the emailed
 * receipt and the in-app printable receipt view.
 */
function generate_receipt_html($order, $student, $departmentName) {
    $itemLabel = $order['order_type'] === 'cloth'
        ? "Departmental Cloth ({$order['yards']} yard" . ($order['yards'] > 1 ? 's' : '') . ")"
        : "Association Dues";

    $date = date('d M Y, h:i A', strtotime($order['created_at']));

    return "
    <div style='font-family:Segoe UI,Arial,sans-serif;max-width:520px;margin:auto;border:1px solid #eee;border-radius:10px;overflow:hidden;'>
      <div style='background:#4a0e18;color:#c9a24b;padding:20px;text-align:center;'>
        <h2 style='margin:0;'>Central University Business School Association</h2>
        <p style='margin:4px 0 0;color:#f6ede2;font-size:13px;'>Official Payment Receipt</p>
      </div>
      <div style='padding:24px;color:#201414;'>
        <p><strong>Receipt No:</strong> #{$order['id']}</p>
        <p><strong>Date:</strong> {$date}</p>
        <p><strong>Student ID:</strong> {$student['student_id']}</p>
        <p><strong>Name/Email:</strong> {$student['email']}</p>
        <p><strong>Department:</strong> {$departmentName}</p>
        <hr style='border:none;border-top:1px solid #eee;margin:16px 0;'>
        <table style='width:100%;border-collapse:collapse;font-size:14px;'>
          <tr>
            <td style='padding:6px 0;'>Item</td>
            <td style='padding:6px 0;text-align:right;'>{$itemLabel}</td>
          </tr>
          <tr>
            <td style='padding:6px 0;'>Unit Price</td>
            <td style='padding:6px 0;text-align:right;'>GHS " . number_format($order['unit_price'], 2) . "</td>
          </tr>
          <tr>
            <td style='padding:6px 0;font-weight:700;'>Total Paid</td>
            <td style='padding:6px 0;text-align:right;font-weight:700;'>GHS " . number_format($order['amount'], 2) . "</td>
          </tr>
        </table>
        <hr style='border:none;border-top:1px solid #eee;margin:16px 0;'>
        <p><strong>Payment Reference:</strong> {$order['paystack_reference']}</p>
        <p><strong>Status:</strong> " . ucfirst($order['status']) . "</p>
        <p style='margin-top:20px;font-size:12px;color:#6b6b6b;text-align:center;'>
          Thank you for your payment. This is an automatically generated receipt.
        </p>
      </div>
    </div>";
}
