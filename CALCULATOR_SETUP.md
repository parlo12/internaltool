# Rental Property Investment Calculator - Setup Instructions

## Installation Steps

### 1. Install NPM Dependencies
```bash
npm install
```

This will install the required `html2canvas` package along with other dependencies.

### 2. Create Storage Symlink
Run the following Artisan command to create a symbolic link from `public/storage` to `storage/app/public`:

```bash
php artisan storage:link
```

### 3. Create Calculations Directory
Ensure the calculations directory exists in storage:

```bash
mkdir -p storage/app/public/calculations
```

### 4. Set Storage Permissions (Linux/Mac)
```bash
chmod -R 775 storage/app/public/calculations
```

For Windows, ensure the web server user has write permissions to the `storage/app/public/calculations` directory.

### 5. Compile Assets
Build the frontend assets:

```bash
npm run build
```

Or for development:

```bash
npm run dev
```

## Verification

### Test the Calculator
1. Navigate to `/property-calculator` in your browser
2. Modify the input values to see real-time calculations
3. Click "Download Screenshot" to test local download
4. Click "Generate Share Link" to test server upload

### Check Storage
After generating a share link, verify:
- Image is saved in `storage/app/public/calculations/`
- Image is accessible via `public/storage/calculations/`

## API Endpoints

### Upload Screenshot
**POST** `/api/calculator/share`

Request:
```json
{
  "image": "data:image/png;base64,..."
}
```

Response:
```json
{
  "success": true,
  "url": "http://yoursite.com/storage/calculations/calculation_xxxxx.png",
  "path": "calculations/calculation_xxxxx.png"
}
```

## File Structure

```
app/
  Http/
    Controllers/
      ShareController.php

routes/
  web.php (contains calculator routes)

resources/
  js/
    Pages/
      PropertyCalculator.tsx
    Components/
      Calculator/
        FinancialInput.tsx
        ShareModal.tsx
    Hooks/
      useCalculator.ts

storage/
  app/
    public/
      calculations/  (screenshots stored here)

public/
  storage/  (symlink to storage/app/public)
```

## Features

### Investment Calculations
- **Cash on Cash ROI**: Annual cashflow / Total cash invested
- **Total ROI**: (Annual cashflow + Principal paydown) / Cash invested
- **Cap Rate**: Net Operating Income / Property price
- **Monthly Cashflow**: Rent - Total monthly expenses
- **Monthly Mortgage**: Amortization formula

### Screenshot Sharing
- Download calculator as PNG image
- Generate shareable link with server storage
- Copy share link to clipboard

### Real-time Updates
- All calculations update instantly on input change
- No page reloads required
- Mobile-responsive design

## Troubleshooting

### Storage Link Issues
If images aren't accessible:
```bash
php artisan storage:link
```

### Permission Errors
Windows:
- Check IIS/Apache user has write access to `storage/app/public`

Linux/Mac:
```bash
sudo chown -R www-data:www-data storage/app/public
chmod -R 775 storage/app/public
```

### Build Errors
Clear cache and rebuild:
```bash
npm run build
php artisan config:clear
php artisan cache:clear
```

## Production Deployment

1. Run production build:
```bash
npm run build
```

2. Optimize Laravel:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Ensure storage directory permissions
4. Verify symbolic link exists
5. Test screenshot generation and sharing

## Browser Compatibility

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support (clipboard API may require user interaction)
- Mobile: Responsive design, touch-friendly controls
