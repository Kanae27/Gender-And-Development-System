<?php
// Enable error reporting for debugging but don't show to users
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Check if FPDF library is available
if (!file_exists('../fpdf186/fpdf.php')) {
    // Simple message for missing library
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Error - FPDF Library Missing</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h4><i class="fas fa-exclamation-triangle me-2"></i>Error</h4>
                        </div>
                        <div class="card-body">
                            <p class="card-text">The FPDF library is missing. Please ensure the fpdf186 folder is in the correct location.</p>
                            <a href="gad_proposal.php" class="btn btn-primary">Return to GAD Proposal Form</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
    exit;
}

require_once('../fpdf186/fpdf.php');
require_once('../includes/db_connection.php');

// Function to show error message
function showError($message) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Error - GAD Proposal Print</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h4><i class="fas fa-exclamation-triangle me-2"></i>Error</h4>
                        </div>
                        <div class="card-body">
                            <p class="card-text">' . htmlspecialchars($message) . '</p>
                            <a href="gad_proposal.php" class="btn btn-primary">Return to GAD Proposal Form</a>
                            <a href="print_html.php?id=' . (isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '') . '" class="btn btn-success">Try HTML Version Instead</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
    exit;
}

// Check if proposal ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    showError('Proposal ID is required to print the document.');
}

$proposalId = intval($_GET['id']);
$debug_file = __DIR__ . '/print_debug.log';

try {
    // Log debug info
    file_put_contents($debug_file, "Print request started for proposal ID: $proposalId at " . date('Y-m-d H:i:s') . "\n");
    
    // Get proposal data
    $sql = "SELECT * FROM gad_proposals WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $proposalId]);
    $proposal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proposal) {
        file_put_contents($debug_file, "Error: Proposal with ID $proposalId not found\n", FILE_APPEND);
        showError('Proposal not found. The requested proposal may have been deleted or does not exist.');
    }
    
    // Get activities data
    $activitySql = "SELECT * FROM gad_proposal_activities WHERE proposal_id = :id ORDER BY sequence ASC";
    $activityStmt = $conn->prepare($activitySql);
    $activityStmt->execute([':id' => $proposalId]);
    $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get personnel data
    $personnelSql = "SELECT gpp.id, gpp.personnel_id, gpp.role, 
                     COALESCE(pl.name, pp.personnel_name) as name, 
                     COALESCE(pl.gender, 'Unspecified') as gender,
                     ar.rank_name
                     FROM gad_proposal_personnel gpp
                     LEFT JOIN personnel_list pl ON gpp.personnel_id = pl.id
                     LEFT JOIN ppas_personnel pp ON pp.personnel_id = gpp.personnel_id AND pp.ppas_id = :ppas_id
                     LEFT JOIN academic_rank ar ON pl.academic_rank_id = ar.id
                     WHERE gpp.proposal_id = :id
                     ORDER BY gpp.role ASC, COALESCE(pl.name, pp.personnel_name) ASC";
    $personnelStmt = $conn->prepare($personnelSql);
    $personnelStmt->execute([
        ':id' => $proposalId,
        ':ppas_id' => $proposal['ppas_id'] ?? null
    ]);
    $personnel = $personnelStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no personnel found through the join and we have a PPAS ID, try direct query
    if (empty($personnel) && !empty($proposal['ppas_id'])) {
        $ppasPersonnelSql = "SELECT id, personnel_id, role, personnel_name as name, 
                          'Unspecified' as gender, NULL as rank_name
                          FROM ppas_personnel 
                          WHERE ppas_id = :ppas_id";
        $ppasPersonnelStmt = $conn->prepare($ppasPersonnelSql);
        $ppasPersonnelStmt->execute([':ppas_id' => $proposal['ppas_id']]);
        $personnel = $ppasPersonnelStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Group personnel by role
    $groupedPersonnel = [
        'project_leader' => [],
        'assistant_project_leader' => [],
        'project_staff' => []
    ];

    // If we have no personnel data but have names stored directly in the proposal fields,
    // use those as a fallback
    if (empty($personnel)) {
        // Project leaders
        if (!empty($proposal['project_leaders'])) {
            $leaders = explode(',', $proposal['project_leaders']);
            foreach ($leaders as $leader) {
                $groupedPersonnel['project_leader'][] = [
                    'name' => trim($leader),
                    'gender' => 'Unspecified',
                    'role' => 'project_leader'
                ];
            }
        }
        
        // Assistant project leaders
        if (!empty($proposal['assistant_project_leaders'])) {
            $assistants = explode(',', $proposal['assistant_project_leaders']);
            foreach ($assistants as $assistant) {
                $groupedPersonnel['assistant_project_leader'][] = [
                    'name' => trim($assistant),
                    'gender' => 'Unspecified',
                    'role' => 'assistant_project_leader'
                ];
            }
        }
        
        // Project staff
        if (!empty($proposal['project_staff'])) {
            $staff = explode(',', $proposal['project_staff']);
            foreach ($staff as $member) {
                $groupedPersonnel['project_staff'][] = [
                    'name' => trim($member),
                    'gender' => 'Unspecified',
                    'role' => 'project_staff'
                ];
            }
        }
    } else {
        foreach ($personnel as $person) {
            // Handle role mapping for ppas_personnel
            $role = $person['role'];
            if ($role == 'project_leader' || $role == 'assistant_project_leader' || $role == 'project_staff') {
                $groupedPersonnel[$role][] = $person;
            } else if ($role == 'asst_project_leader') {
                $groupedPersonnel['assistant_project_leader'][] = $person;
            }
        }
    }

} catch (PDOException $e) {
    // Log the error but don't show database details to the user
    error_log('PDF Generation Error: ' . $e->getMessage());
    file_put_contents($debug_file, "Database error: " . $e->getMessage() . "\n", FILE_APPEND);
    showError('A database error occurred while fetching proposal data. Please try again later or contact support.');
}

// Create custom PDF class
class GADPDF extends FPDF
{
    private $proposal;
    private $pageTitle;

    function __construct($pageTitle, $proposal, $orientation='P', $unit='mm', $size='A4') 
    {
        parent::__construct($orientation, $unit, $size);
        $this->proposal = $proposal;
        $this->pageTitle = $pageTitle;
        $this->SetAutoPageBreak(true, 20);
    }

    function Header()
    {
        // Draw borders
        $this->SetLineWidth(0.8);
        $this->Rect(8, 8, 194, 281);
        $this->SetLineWidth(0.3);
        $this->Rect(11, 11, 188, 275);

        // Logo - try multiple possible paths
        $logoPaths = [
            '../images/logo.png',
            '../images/Batangas_State_Logo.png',
            '../images/loading_screen_logo.png'
        ];
        
        $logoFound = false;
        foreach ($logoPaths as $logoPath) {
            if (file_exists($logoPath)) {
                $this->Image($logoPath, 15, 15, 20);
                $logoFound = true;
                break;
            }
        }
        
        if (!$logoFound) {
            // If no logo exists, leave space for it
            $this->Cell(20, 20, '', 0, 0);
        }
        
        // Institution name and title
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, 'BATANGAS STATE UNIVERSITY', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, 'The National Engineering University', 0, 1, 'C');
        $this->Cell(0, 5, 'GAD Office', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'GENDER AND DEVELOPMENT (GAD) PROPOSAL', 0, 1, 'C');
        
        // Line break and horizontal line
        $this->Ln(5);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(5);
    }

    function Footer()
    {
        // Ensure border extends to bottom of page
        $this->SetLineWidth(0.8);
        $this->Rect(8, 8, 194, 281);
        $this->SetLineWidth(0.3);
        $this->Rect(11, 11, 188, 275);

        // Page number
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, $title, 0, 1, 'L');
        $this->Ln(2);
    }

    function ContentCell($w, $h, $txt, $border=0, $ln=1, $align='L', $fill=false)
    {
        $this->SetFont('Arial', '', 10);
        $this->MultiCell($w, $h, $txt, $border, $align, $fill);
    }

    function TableHeader($w, $h, $txt, $border=1, $ln=1, $align='C', $fill=true)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill);
    }

    function TableCell($w, $h, $txt, $border=1, $ln=1, $align='L', $fill=false)
    {
        $this->SetFont('Arial', '', 10);
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill);
    }
}

// Function to display HTML version as fallback
function generateHtmlVersion($proposal, $activities, $groupedPersonnel) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>GAD Proposal - <?php echo htmlspecialchars($proposal['title']); ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 20px;
                border: 3px double #000;
                padding: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 1px solid #000;
                padding-bottom: 10px;
            }
            .section {
                margin-bottom: 20px;
            }
            .section-title {
                font-weight: bold;
                margin-bottom: 5px;
            }
            .section-content {
                border: 1px solid #000;
                padding: 10px;
            }
            .signature {
                margin-top: 50px;
                display: flex;
                justify-content: space-between;
            }
            .signature-box {
                width: 45%;
            }
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 10px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            @media print {
                .print-button {
                    display: none;
                }
                body {
                    margin: 0;
                    border: 1px solid #000;
                }
            }
        </style>
    </head>
    <body>
        <button class="print-button" onclick="window.print()">Print Document</button>
        
        <div class="header">
            <h2>BATANGAS STATE UNIVERSITY</h2>
            <h3>The National Engineering University</h3>
            <h3>GAD Office</h3>
            <h2>GENDER AND DEVELOPMENT (GAD) PROPOSAL</h2>
        </div>
        
        <div class="section">
            <h1 style="text-align: center;"><?php echo htmlspecialchars($proposal['title']); ?></h1>
        </div>
        
        <div class="section">
            <div class="section-title">PROJECT TEAM</div>
            <div class="section-content">
                <?php 
                if (!empty($groupedPersonnel['project_leader'])) {
                    foreach ($groupedPersonnel['project_leader'] as $leader) {
                        echo "<p>".htmlspecialchars($leader['name'])."</p>";
                    }
                } else {
                    echo "<p>".htmlspecialchars($proposal['project_leader'] ?? 'Not specified')."</p>";
                }
                if (!empty($groupedPersonnel['assistant_project_leader'])) {
                    foreach ($groupedPersonnel['assistant_project_leader'] as $assistant) {
                        echo "<p>".htmlspecialchars($assistant['name'])."</p>";
                    }
                } else {
                    echo "<p>".htmlspecialchars($proposal['assistant_project_leader'] ?? 'Not specified')."</p>";
                }
                if (!empty($groupedPersonnel['project_staff'])) {
                    foreach ($groupedPersonnel['project_staff'] as $staff) {
                        echo "<p>".htmlspecialchars($staff['name'])."</p>";
                    }
                } else {
                    echo "<p>".htmlspecialchars($proposal['project_staff'] ?? 'Not specified')."</p>";
                }
                ?>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">PARTNER OFFICES</div>
            <div class="section-content">
                <p><?php echo htmlspecialchars($proposal['partner_offices'] ?? 'Not specified'); ?></p>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">TYPE OF PARTICIPANTS</div>
            <div class="section-content">
                <p><?php echo htmlspecialchars($proposal['participants'] ?? 'Not specified'); ?></p>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">RATIONALE</div>
            <div class="section-content">
                <p><?php echo nl2br(htmlspecialchars($proposal['rationale'] ?? 'Not specified')); ?></p>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">OBJECTIVES</div>
            <div class="section-content">
                <p><?php echo nl2br(htmlspecialchars($proposal['objectives'] ?? 'Not specified')); ?></p>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">STRATEGIES</div>
            <div class="section-content">
                <p><?php echo nl2br(htmlspecialchars($proposal['strategies'] ?? 'Not specified')); ?></p>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">WORK PLAN</div>
            <div class="section-content">
                <p><?php echo nl2br(htmlspecialchars($proposal['work_plan'] ?? 'Not specified')); ?></p>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">FINANCIAL REQUIREMENTS</div>
            <div class="section-content">
                <p><?php echo nl2br(htmlspecialchars($proposal['financial_requirements'] ?? 'Not specified')); ?></p>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">SUSTAINABILITY PLAN</div>
            <div class="section-content">
                <p><?php echo nl2br(htmlspecialchars($proposal['sustainability_plan'] ?? 'Not specified')); ?></p>
            </div>
        </div>
        
        <div class="signature">
            <div class="signature-box">
                <p style="text-align: left;">Prepared by:</p>
                <br><br><br>
                <p style="text-align: left;"><b><?php echo htmlspecialchars($proposal['project_leader'] ?? 'PROJECT LEADER'); ?></b></p>
                <p style="text-align: left;">Project Leader</p>
            </div>
            <div class="signature-box">
                <p style="text-align: left;">Approved by:</p>
                <br><br><br>
                <p style="text-align: left;"><b>GAD Focal Person</b></p>
                <p style="text-align: left;">&nbsp;</p>
            </div>
        </div>
        
        <script>
            // Print automatically when page loads
            window.onload = function() {
                // Wait a moment for styles to apply
                setTimeout(function() {
                    // Uncomment the line below to auto-print
                    // window.print();
                }, 1000);
            };
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

try {
    // Initialize PDF document
    $pdf = new GADPDF('GAD Proposal', $proposal);
    $pdf->AliasNbPages();
    $pdf->SetMargins(15, 15, 15);
    
    // Log progress
    file_put_contents($debug_file, "PDF initialization successful, adding first page\n", FILE_APPEND);
    
    $pdf->AddPage();
    
    // Log progress
    file_put_contents($debug_file, "First page added, generating content\n", FILE_APPEND);
    
    // Title Section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 5, $proposal['title'], 0, 1, 'C');
    $pdf->Ln(5);

    // Project Team Section
    $pdf->SectionTitle('PROJECT TEAM');
    $pdf->TableHeader(190, 5, 'Name', 1, 1, 'L', true);
    $pdf->TableCell(190, 5, $proposal['project_leader'], 1, 1, 'L');
    $pdf->TableCell(190, 5, $proposal['assistant_project_leader'], 1, 1, 'L');
    $pdf->TableCell(190, 5, $proposal['project_staff'], 1, 1, 'L');
    $pdf->Ln(5);

    // Partner Offices Section
    $pdf->SectionTitle('PARTNER OFFICES');
    $pdf->TableHeader(190, 5, 'Office', 1, 1, 'L', true);
    $pdf->TableCell(190, 5, $proposal['partner_offices'], 1, 1, 'L');
    $pdf->Ln(5);

    // Type of Participants Section
    $pdf->SectionTitle('TYPE OF PARTICIPANTS');
    $pdf->TableHeader(190, 5, 'Participants', 1, 1, 'L', true);
    $pdf->TableCell(190, 5, $proposal['participants'], 1, 1, 'L');
    $pdf->Ln(5);

    // Rationale Section
    $pdf->SectionTitle('RATIONALE');
    $pdf->TableHeader(190, 5, 'Rationale', 1, 1, 'L', true);
    $pdf->TableCell(190, 5, $proposal['rationale'], 1, 1, 'L');
    $pdf->Ln(5);

    // Objectives Section
    $pdf->SectionTitle('OBJECTIVES');
    $pdf->TableHeader(190, 5, 'Objectives', 1, 1, 'L', true);
    $pdf->TableCell(190, 5, $proposal['objectives'], 1, 1, 'L');
    $pdf->Ln(5);

    // Strategies Section
    $pdf->SectionTitle('STRATEGIES');
    $pdf->TableHeader(190, 5, 'Strategies', 1, 1, 'L', true);
    $pdf->TableCell(190, 5, $proposal['strategies'], 1, 1, 'L');
    $pdf->Ln(5);

    // Work Plan Section
    $pdf->SectionTitle('WORK PLAN');
    $pdf->TableHeader(190, 5, 'Work Plan', 1, 1, 'L', true);
    $pdf->TableCell(190, 5, $proposal['work_plan'], 1, 1, 'L');
    $pdf->Ln(5);

    // Financial Requirements Section
    $pdf->SectionTitle('FINANCIAL REQUIREMENTS');
    $pdf->TableHeader(190, 5, 'Financial Requirements', 1, 1, 'L', true);
    $pdf->TableCell(190, 5, $proposal['financial_requirements'], 1, 1, 'L');
    $pdf->Ln(5);

    // Sustainability Plan Section
    $pdf->SectionTitle('SUSTAINABILITY PLAN');
    $pdf->TableHeader(190, 5, 'Sustainability Plan', 1, 1, 'L', true);
    $pdf->TableCell(190, 5, $proposal['sustainability_plan'], 1, 1, 'L');
    $pdf->Ln(5);

    // Signature Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(95, 5, 'Prepared by:', 0, 0, 'L');
    $pdf->Cell(95, 5, 'Approved by:', 0, 1, 'L');
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(95, 5, $proposal['project_leader'], 0, 0, 'L');
    $pdf->Cell(95, 5, 'GAD Focal Person', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(95, 5, 'Project Leader', 0, 0, 'L');
    $pdf->Cell(95, 5, '', 0, 1, 'L');

    // Log progress before output
    file_put_contents($debug_file, "PDF content generation complete, preparing to output\n", FILE_APPEND);
    
    // Filename for the PDF
    $filename = 'GAD_Proposal_' . $proposalId . '.pdf';
    
    // Clear any previous output
    if (ob_get_contents()) ob_end_clean();
    
    // Set headers for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Log output attempt
    file_put_contents($debug_file, "Headers set, attempting PDF output\n", FILE_APPEND);
    
    // Output the PDF
    $pdf->Output($filename, 'D');
    
    // Log successful completion (this likely won't be reached due to exit)
    file_put_contents($debug_file, "PDF generated and output successfully\n", FILE_APPEND);
    exit;
    
} catch (Exception $e) {
    // Log the detailed error message
    error_log('PDF Generation Error: ' . $e->getMessage());
    file_put_contents($debug_file, "Error generating PDF: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($debug_file, "Error trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    // Try to generate an HTML version as fallback
    file_put_contents($debug_file, "Attempting to generate HTML version as fallback\n", FILE_APPEND);
    
    try {
        $htmlOutput = generateHtmlVersion($proposal, $activities, $groupedPersonnel);
        // Add a message at the top of the HTML output
        $messageHtml = '
        <div style="background-color: #e8f5e9; border: 1px solid #4caf50; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <h4 style="color: #2e7d32; margin-top: 0;">HTML Version Displayed</h4>
            <p>The PDF version could not be generated due to an error. This is a fully printable HTML alternative.</p>
            <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <button onclick="window.print()" style="background-color: #4caf50; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                <i class="fas fa-print"></i> Print This Document
            </button>
            <a href="gad_proposal.php" style="background-color: #2196f3; color: white; border: none; padding: 8px 15px; border-radius: 4px; text-decoration: none; margin-left: 10px;">
                Return to GAD Proposal Form
            </a>
        </div>';
        
        // Insert the message after the <body> tag
        $htmlOutput = preg_replace('/<body>/', '<body>' . $messageHtml, $htmlOutput);
        
        echo $htmlOutput;
        file_put_contents($debug_file, "HTML fallback generated successfully\n", FILE_APPEND);
        exit;
    } catch (Exception $htmlError) {
        file_put_contents($debug_file, "Error generating HTML fallback: " . $htmlError->getMessage() . "\n", FILE_APPEND);
        showError('An error occurred while generating the PDF: ' . $e->getMessage() . '. HTML fallback also failed.');
    }
}
?>