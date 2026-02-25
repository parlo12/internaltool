import React, { useState, useRef } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import FinancialInput from '@/Components/Calculator/FinancialInput';
import ScreenshotView from '@/Components/Calculator/ScreenshotView';
import { useCalculator } from '@/Hooks/useCalculator';
import html2canvas from 'html2canvas';
import axios from 'axios';

export default function PropertyCalculator({ auth }) {
    const screenshotRef = useRef(null);
    const [isGeneratingLink, setIsGeneratingLink] = useState(false);

    const {
        inputs,
        updateInput,
        calculations,
        getExpenseDollarAmount
    } = useCalculator();

    const loanTermOptions = [
        { value: 15, label: '15 years' },
        { value: 20, label: '20 years' },
        { value: 30, label: '30 years' }
    ];

    const captureScreenshot = async () => {
        if (!screenshotRef.current) throw new Error('Screenshot ref not found');

        try {
            // Temporarily move the screenshot view into the viewport
            const originalLeft = screenshotRef.current.style.left;
            const originalTop = screenshotRef.current.style.top;
            const originalPosition = screenshotRef.current.style.position;
            const originalZIndex = screenshotRef.current.style.zIndex;

            screenshotRef.current.style.left = '0';
            screenshotRef.current.style.top = '0';
            screenshotRef.current.style.position = 'absolute';
            screenshotRef.current.style.zIndex = '-9999';

            // Wait for styles to apply and render
            await new Promise(resolve => setTimeout(resolve, 100));

            const canvas = await html2canvas(screenshotRef.current, {
                backgroundColor: '#f3f4f6',
                scale: 2,
                logging: false,
                windowHeight: screenshotRef.current.scrollHeight,
                height: screenshotRef.current.scrollHeight,
                useCORS: true,
                allowTaint: true,
            });

            // Restore original styles
            screenshotRef.current.style.left = originalLeft;
            screenshotRef.current.style.top = originalTop;
            screenshotRef.current.style.position = originalPosition;
            screenshotRef.current.style.zIndex = originalZIndex;

            return canvas.toDataURL('image/png');
        } catch (error) {
            console.error('Error capturing screenshot:', error);
            throw error;
        }
    };

    const handleDownloadScreenshot = async () => {
        try {
            const dataUrl = await captureScreenshot();
            const link = document.createElement('a');
            link.download = `property-calculator-${Date.now()}.png`;
            link.href = dataUrl;
            link.click();
        } catch (error) {
            console.error('Error downloading screenshot:', error);
            alert('Failed to download screenshot');
        }
    };

    const handleShareScreenshot = async () => {
        try {
            setIsGeneratingLink(true);
            const dataUrl = await captureScreenshot();

            // Convert data URL to blob
            const response = await fetch(dataUrl);
            const blob = await response.blob();
            const file = new File([blob], `property-calculator-${Date.now()}.png`, { type: 'image/png' });

            // Check if Web Share API with files is supported
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                await navigator.share({
                    files: [file],
                    title: 'Property Investment Calculator',
                    text: 'Check out my property investment calculator results!',
                });
            } else if (navigator.share) {
                // Fallback to sharing URL
                const uploadResponse = await axios.post('/api/calculator/share', {
                    image: dataUrl,
                });

                if (uploadResponse.data.success) {
                    await navigator.share({
                        title: 'Property Investment Calculator',
                        text: 'Check out my property investment calculator results!',
                        url: uploadResponse.data.url,
                    });
                }
            } else {
                // Fallback to download
                const link = document.createElement('a');
                link.download = `property-calculator-${Date.now()}.png`;
                link.href = dataUrl;
                link.click();
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Error sharing:', error);
                alert('Failed to share');
            }
        } finally {
            setIsGeneratingLink(false);
        }
    };

    const formatCurrency = (value) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    };

    const formatPercent = (value) => {
        return `${value.toFixed(2)}%`;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Property Investment Calculator</h2>}
        >
            <Head title="Property Calculator" />

            <ScreenshotView 
                ref={screenshotRef}
                inputs={inputs}
                calculations={calculations}
                getExpenseDollarAmount={getExpenseDollarAmount}
                formatCurrency={formatCurrency}
                formatPercent={formatPercent}
            />

            <div className="py-4 sm:py-6 bg-gray-100 min-h-screen lg:h-screen lg:overflow-auto">
                <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
                    <div className="bg-white rounded-lg shadow-lg overflow-hidden">
                        {/* Red Summary Header */}
                        <div className="bg-[#d32f2f] text-white p-3 sm:p-4">
                            <div className="grid grid-cols-2 gap-2 sm:gap-4">
                                <div>
                                    <div className="text-[10px] sm:text-xs opacity-90 mb-0.5">Cash on Cash</div>
                                    <div className="text-lg sm:text-2xl md:text-3xl font-bold leading-tight">
                                        {formatPercent(calculations.cashOnCash)}
                                    </div>
                                </div>
                                <div>
                                    <div className="text-[10px] sm:text-xs opacity-90 mb-0.5">Total ROI</div>
                                    <div className="text-lg sm:text-2xl md:text-3xl font-bold leading-tight">
                                        {formatPercent(calculations.totalROI)}
                                    </div>
                                </div>
                                <div>
                                    <div className="text-[10px] sm:text-xs opacity-90 mb-0.5">Monthly Cashflow</div>
                                    <div className="text-lg sm:text-2xl md:text-3xl font-bold leading-tight">
                                        {formatCurrency(calculations.monthlyCashflow)}
                                    </div>
                                    <div className="text-[9px] sm:text-xs opacity-75">
                                        ({formatCurrency(calculations.annualCashflow)}/yr)
                                    </div>
                                </div>
                                <div>
                                    <div className="text-[10px] sm:text-xs opacity-90 mb-0.5">Cap Rate</div>
                                    <div className="text-lg sm:text-2xl md:text-3xl font-bold leading-tight">
                                        {formatPercent(calculations.capRate)}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Calculator Body */}
                        <div className="p-3 sm:p-4 space-y-4">
                            {/* Purchase Details & Income Combined */}
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                {/* Purchase Details */}
                                <div>
                                    <h3 className="text-base font-semibold text-gray-900 mb-2 pb-1 border-b">
                                        Purchase Details
                                    </h3>
                                    <div className="space-y-3">
                                        <FinancialInput
                                            label="Price"
                                            value={inputs.price}
                                            onChange={(val) => updateInput('price', val)}
                                            type="currency"
                                        />
                                        <div className="grid grid-cols-2 gap-3">
                                            <FinancialInput
                                                label="Down Payment"
                                                value={inputs.downPaymentPercent}
                                                onChange={(val) => updateInput('downPaymentPercent', val)}
                                                type="percent"
                                            />
                                            <FinancialInput
                                                label="Interest Rate"
                                                value={inputs.interestRate}
                                                onChange={(val) => updateInput('interestRate', val)}
                                                type="percent"
                                                step={0.1}
                                            />
                                        </div>
                                        <div className="grid grid-cols-3 gap-3">
                                            <div>
                                                <label className="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                                                    Loan Term
                                                </label>
                                                <select
                                                    value={inputs.loanTermYears}
                                                    onChange={(e) => updateInput('loanTermYears', Number(e.target.value))}
                                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm font-semibold text-gray-900"
                                                >
                                                    {loanTermOptions.map(option => (
                                                        <option key={option.value} value={option.value}>
                                                            {option.label}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                            <div className="col-span-2">
                                                <FinancialInput
                                                    label="Closing Costs"
                                                    value={inputs.closingCosts}
                                                    onChange={(val) => updateInput('closingCosts', val)}
                                                    type="currency"
                                                />
                                            </div>
                                        </div>
                                        <FinancialInput
                                            label="Rehab"
                                            value={inputs.rehab}
                                            onChange={(val) => updateInput('rehab', val)}
                                            type="currency"
                                        />
                                    </div>
                                </div>

                                {/* Income & Mortgage */}
                                <div>
                                    <h3 className="text-base font-semibold text-gray-900 mb-2 pb-1 border-b">
                                        Income
                                    </h3>
                                    <div className="grid grid-cols-2 gap-3">
                                        <FinancialInput
                                            label="Monthly Rental Income"
                                            value={inputs.monthlyRent}
                                            onChange={(val) => updateInput('monthlyRent', val)}
                                            type="currency"
                                        />
                                        <div className="bg-gray-50 p-2 rounded-lg border border-gray-200">
                                            <div className="text-xs font-medium text-gray-600 mb-1">Mortgage Payment</div>
                                            <div className="text-base sm:text-lg font-bold text-gray-900 break-words">
                                                {formatCurrency(calculations.monthlyMortgage)}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Monthly Expenses */}
                            <div>
                                <h3 className="text-base font-semibold text-gray-900 mb-2 pb-1 border-b">
                                    Monthly Expenses
                                </h3>
                                <div className="grid grid-cols-2 lg:grid-cols-3 gap-3">
                                    <FinancialInput
                                        label="Property Taxes"
                                        value={inputs.taxes}
                                        onChange={(val) => updateInput('taxes', val)}
                                        type="currency"
                                    />
                                    <FinancialInput
                                        label="Insurance"
                                        value={inputs.insurance}
                                        onChange={(val) => updateInput('insurance', val)}
                                        type="currency"
                                    />
                                    <FinancialInput
                                        label="Utilities"
                                        value={inputs.utilities}
                                        onChange={(val) => updateInput('utilities', val)}
                                        type="currency"
                                    />
                                    <FinancialInput
                                        label="Maintenance"
                                        value={inputs.maintenance}
                                        onChange={(val) => updateInput('maintenance', val)}
                                        type="currency"
                                    />
                                    <FinancialInput
                                        label="Miscellaneous"
                                        value={inputs.miscellaneous}
                                        onChange={(val) => updateInput('miscellaneous', val)}
                                        type="currency"
                                    />
                                    <FinancialInput
                                        label="Capital Expenditure"
                                        value={inputs.capExPercent}
                                        onChange={(val) => updateInput('capExPercent', val)}
                                        type="percent"
                                        displayAmount={getExpenseDollarAmount(inputs.capExPercent)}
                                    />
                                    <FinancialInput
                                        label="Property Management"
                                        value={inputs.propertyManagementPercent}
                                        onChange={(val) => updateInput('propertyManagementPercent', val)}
                                        type="percent"
                                        displayAmount={getExpenseDollarAmount(inputs.propertyManagementPercent)}
                                    />
                                    <FinancialInput
                                        label="Vacancy"
                                        value={inputs.vacancyPercent}
                                        onChange={(val) => updateInput('vacancyPercent', val)}
                                        type="percent"
                                        displayAmount={getExpenseDollarAmount(inputs.vacancyPercent)}
                                    />
                                </div>
                            </div>

                            {/* Share & Download Buttons */}
                            <div className="pt-3 border-t">
                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        onClick={handleDownloadScreenshot}
                                        className="px-4 py-2.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2"
                                    >
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Download
                                    </button>
                                    <button
                                        onClick={handleShareScreenshot}
                                        disabled={isGeneratingLink}
                                        className="px-4 py-2.5 bg-[#d32f2f] hover:bg-[#b71c1c] text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                    >
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                        </svg>
                                        {isGeneratingLink ? 'Preparing...' : 'Share'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
