# GAD Proposal Print Module

This module provides functionality to print GAD proposals from the GAD system.

## Overview

The Print Proposal module allows users to:

1. Select a campus, year, and specific proposal
2. View a preview of the proposal
3. Print the proposal directly
4. Export the proposal to a Word document

## Files and Structure

- `print_proposal.php` - Main file for displaying the interface
- `api/` - Directory containing API endpoints
  - `get_campuses.php` - Get available campuses
  - `get_proposal_years.php` - Get years for a selected campus
  - `get_proposals.php` - Get proposals for a selected campus and year
  - `get_proposal_details.php` - Get detailed information for a specific proposal
- `config.php` - Local configuration file (fallback if main config is inaccessible)
- `images/` - Directory containing images like logos

## Installation

1. Copy this entire folder to your GAD system root directory
2. Ensure proper database connectivity in `config.php`
3. Verify that the module can access the database tables: `gad_proposals`, `ppas_forms`, `ppas_personnel`, and `personnel`

## Usage

1. Access the module via the URL: `/print_proposal_module/print_proposal.php`
2. Select a campus from the dropdown menu
3. Select a year from the dropdown menu (populates after campus selection)
4. Search for and select a proposal
5. Click "Generate Proposal" to view the preview
6. Use the "Print" or "Word" buttons to output the proposal

## Dependencies

This module depends on:
- jQuery 3.6.0
- Bootstrap 5.3.0
- Font Awesome 6.4.0
- SweetAlert2
- Select2

## Database Structure

The module interacts with the following database tables:
- `gad_proposals` - Contains proposal information
- `ppas_forms` - Contains basic form information
- `ppas_personnel` - Contains personnel assignments
- `personnel` - Contains personnel details
- `signatories` - Contains signature information

## Troubleshooting

If you encounter issues:
1. Check database connectivity in `config.php`
2. Verify file permissions allow PHP to read/write
3. Check browser console for JavaScript errors
4. Review PHP error logs for backend issues

## Credits

This module was created for the GAD system. 