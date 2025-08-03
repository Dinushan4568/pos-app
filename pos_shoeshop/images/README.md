# Images Directory

This directory contains images used in the POS system.

## Logo Image

To add your store logo to receipts:

1. Place your logo image file in this directory
2. Name it `logo.png` (or update the path in `receipt.php`)
3. Recommended size: 200x200 pixels or smaller
4. Supported formats: PNG, JPG, GIF

## Current Files

- `logo.png` - Store logo for receipts (you need to add this)

## How to Update Logo

1. Replace the existing `logo.png` file with your new logo
2. Or update the image path in `receipt.php` line 67:
   ```html
   <img src="images/your-logo.png" alt="Store Logo" class="h-16 mx-auto">
   ```

## Receipt Customization

You can also customize other receipt details in `receipt.php`:
- Store name
- Address
- Phone number
- Email
- Return policy 