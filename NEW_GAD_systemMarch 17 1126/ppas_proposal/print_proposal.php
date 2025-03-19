<?php
session_start();
require_once '../includes/db_connection.php';

// Check if proposal ID is provided
if (!isset($_GET['id'])) {
    die('Proposal ID is required');
}

$proposalId = $_GET['id'];

try {
    // Fetch proposal data
    $stmt = $conn->prepare("
        SELECT * FROM gad_proposals WHERE id = :id
    ");
    $stmt->execute(['id' => $proposalId]);
    $proposal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proposal) {
        die('Proposal not found');
    }

    // Fetch project team data
    $teamStmt = $conn->prepare("
        SELECT * FROM gad_project_team WHERE proposal_id = :proposal_id
    ");
    $teamStmt->execute(['proposal_id' => $proposalId]);
    $teamMembers = $teamStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch activities
    $activityStmt = $conn->prepare("
        SELECT * FROM gad_activities WHERE proposal_id = :proposal_id
    ");
    $activityStmt->execute(['proposal_id' => $proposalId]);
    $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die('Error fetching proposal data: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAD Proposal - Print View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reset margins for printing */
        @page {
            margin: 0.5in;
            size: letter portrait;
        }

        /* Print-specific styles */
        @media print {
            body {
                padding: 0;
                margin: 0;
                font-size: 12pt;
                line-height: 1.3;
                background: #fff;
                color: #000;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            .header {
                position: running(header);
                width: 100%;
            }
            .content {
                margin-top: 1.5in;
            }
            table {
                page-break-inside: avoid;
                border-collapse: collapse;
                width: 100%;
            }
            .signature-section {
                page-break-inside: avoid;
            }
        }

        /* General styles */
        body {
            font-family: "Times New Roman", Times, serif;
            line-height: 1.6;
            color: #000;
            background: #fff;
            font-size: 12pt;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #000;
            padding-bottom: 1rem;
        }

        .university-name {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .document-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .logo {
            max-width: 80px;
            margin-bottom: 0.5rem;
        }

        .section {
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .subsection-title {
            font-weight: bold;
            font-size: 12pt;
            margin: 1rem 0 0.5rem 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        th, td {
            border: 1px solid #000;
            padding: 0.5rem;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .print-button:hover {
            background-color: #0056b3;
        }

        .signature-section {
            margin-top: 3rem;
            page-break-inside: avoid;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 2rem;
            margin-bottom: 0.5rem;
        }

        .indent {
            margin-left: 2rem;
        }

        .text-justify {
            text-align: justify;
        }

        .page-number:before {
            content: counter(page);
        }

        .page-count:before {
            content: counter(pages);
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn btn-primary print-button no-print">
        <i class="fas fa-print"></i> Print Proposal
    </button>

    <div class="header">
        <img src="../images/Batangas_State_Logo.png" alt="BatState-U Logo" class="logo">
        <div class="university-name">BATANGAS STATE UNIVERSITY</div>
        <div class="document-title">GENDER AND DEVELOPMENT (GAD) ACTIVITY PROPOSAL</div>
        <div>Academic Year <?php echo htmlspecialchars($proposal['year']); ?> - Quarter <?php echo htmlspecialchars($proposal['quarter']); ?></div>
    </div>

    <div class="content">
        <div class="section">
            <div class="section-title">TYPE OF PROPOSAL</div>
            <div class="indent"><?php echo ucfirst(htmlspecialchars($proposal['proposal_type'])); ?></div>
        </div>

        <div class="section">
            <div class="section-title">I. TITLE</div>
            <div class="indent text-justify"><?php echo htmlspecialchars($proposal['title']); ?></div>
        </div>

        <div class="section">
            <div class="section-title">II. SCHEDULE AND VENUE</div>
            <div class="indent">
                <p><strong>Start Date:</strong> <?php echo date('F d, Y', strtotime($proposal['start_date'])); ?></p>
                <p><strong>End Date:</strong> <?php echo date('F d, Y', strtotime($proposal['end_date'])); ?></p>
                <p><strong>Venue:</strong> <?php echo htmlspecialchars($proposal['venue']); ?></p>
            </div>
        </div>

        <div class="section">
            <div class="section-title">III. MODE OF DELIVERY</div>
            <div class="indent"><?php echo ucfirst(htmlspecialchars($proposal['delivery_mode'])); ?></div>
        </div>

        <div class="section page-break">
            <div class="section-title">IV. PROJECT TEAM</div>
            <div class="indent">
                <?php foreach ($teamMembers as $member): ?>
                    <div class="mb-3">
                        <strong><?php echo ucwords(str_replace('_', ' ', $member['role'])); ?>:</strong>
                        <p><?php echo htmlspecialchars($member['personnel_name']); ?></p>
                        <?php if (!empty($member['responsibilities'])): ?>
                            <p class="text-justify"><strong>Responsibilities:</strong><br>
                            <?php echo nl2br(htmlspecialchars($member['responsibilities'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">V. PARTNER OFFICE/COLLEGE/DEPARTMENT</div>
            <div class="indent"><?php echo htmlspecialchars($proposal['partner_offices']); ?></div>
        </div>

        <div class="section">
            <div class="section-title">VI. TYPE OF PARTICIPANTS</div>
            <div class="indent">
                <table>
                    <thead>
                        <tr>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $proposal['male_beneficiaries']; ?></td>
                            <td><?php echo $proposal['female_beneficiaries']; ?></td>
                            <td><?php echo $proposal['total_beneficiaries']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section page-break">
            <div class="section-title">VII. RATIONALE/BACKGROUND</div>
            <div class="indent text-justify">
                <?php echo nl2br(htmlspecialchars($proposal['rationale'])); ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">VIII. OBJECTIVES</div>
            <div class="indent text-justify">
                <?php echo nl2br(htmlspecialchars($proposal['specific_objectives'])); ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">IX. DESCRIPTION, STRATEGIES, AND METHODS</div>
            <div class="indent">
                <div class="subsection-title">Strategies:</div>
                <div class="text-justify">
                    <?php echo nl2br(htmlspecialchars($proposal['strategies'])); ?>
                </div>
                
                <div class="subsection-title">Activities:</div>
                <?php foreach ($activities as $index => $activity): ?>
                    <div class="mb-3">
                        <strong><?php echo ($index + 1) . '. ' . htmlspecialchars($activity['activity_title']); ?></strong>
                        <div class="text-justify">
                            <?php echo nl2br(htmlspecialchars($activity['activity_details'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section page-break">
            <div class="section-title">X. FINANCIAL REQUIREMENTS</div>
            <div class="indent">
                <p><strong>Source of Budget:</strong> <?php echo htmlspecialchars($proposal['budget_source']); ?></p>
                <p><strong>Total Budget:</strong> ₱<?php echo number_format($proposal['total_budget'], 2); ?></p>
                <div class="mt-3">
                    <strong>Budget Breakdown:</strong>
                    <div class="text-justify">
                        <?php echo nl2br(htmlspecialchars($proposal['budget_breakdown'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">XI. SUSTAINABILITY PLAN</div>
            <div class="indent text-justify">
                <?php echo nl2br(htmlspecialchars($proposal['sustainability_plan'])); ?>
            </div>
        </div>

        <div class="signature-section">
            <div class="row">
                <div class="col-6">
                    <p>Prepared by:</p>
                    <div class="signature-line"></div>
                    <strong>Project Leader</strong>
                </div>
                <div class="col-6 text-end">
                    <p>Approved by:</p>
                    <div class="signature-line" style="margin-left: auto;"></div>
                    <strong>Head of Office</strong>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add page numbers when printing
        window.onbeforeprint = function() {
            var pageCount = Math.ceil(document.body.scrollHeight / 1056); // Approximate A4 height in pixels
            var pageNumbers = document.createElement('div');
            pageNumbers.style.position = 'fixed';
            pageNumbers.style.bottom = '20px';
            pageNumbers.style.right = '20px';
            pageNumbers.style.fontSize = '10pt';
            pageNumbers.innerHTML = 'Page <span class="page-number"></span> of <span class="page-count"></span>';
            document.body.appendChild(pageNumbers);
        };
    </script>
</body>
</html> 