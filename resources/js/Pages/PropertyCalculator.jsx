import React, { useState, useRef } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import FinancialInput from '@/Components/Calculator/FinancialInput';
import { useCalculator } from '@/Hooks/useCalculator';
import html2canvas from 'html2canvas';
import axios from 'axios';

export default function PropertyCalculator({ auth }) {
    const calculatorRef = useRef(null);
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
        if (!calculatorRef.current) throw new Error('Calculator ref not found');

        // Store original styles
        const parentContainer = calculatorRef.current.closest('.lg\\:h-screen');
        const originalMaxHeight = parentContainer?.style.maxHeight;
        const originalHeight = parentContainer?.style.height;
        const originalOverflow = parentContainer?.style.overflow;

        // Store references to inputs and selects to replace them temporarily
        const inputs = Array.from(calculatorRef.current.querySelectorAll('input[type="text"]'));
        const selects = Array.from(calculatorRef.current.querySelectorAll('select'));
        const replacements = [];

        try {
            // Add screenshot mode class
            calculatorRef.current.classList.add('screenshot-mode');

            // Replace inputs with divs (including currency/percent symbols)
            inputs.forEach(input => {
                const parent = input.closest('.relative');
                if (parent) {
                    const currencySymbol = parent.querySelector('span:first-child');
                    const percentSymbol = parent.querySelector('span:last-child');
                    
                    const div = document.createElement('div');
                    let text = '';
                    
                    if (currencySymbol && currencySymbol.textContent.trim() === '$') {
                        text = '$ ' + input.value;
                        currencySymbol.style.display = 'none';
                        replacements.push({ original: currencySymbol, type: 'show' });
                    } else if (percentSymbol && percentSymbol.textContent.trim() === '%') {
                        text = input.value + ' %';
                        percentSymbol.style.display = 'none';
                        replacements.push({ original: percentSymbol, type: 'show' });
                    } else {
                        text = input.value;
                    }
                    
                    div.textContent = text;
                    div.style.cssText = 'display: inline; font-weight: 600; color: #111827; font-size: 14px;';
                    parent.appendChild(div);
                    input.style.display = 'none';
                    replacements.push({ original: input, replacement: div });
                }
            });

            // Replace selects with divs
            selects.forEach(select => {
                const div = document.createElement('div');
                div.textContent = select.options[select.selectedIndex].text;
                div.style.cssText = 'display: inline; font-weight: 600; color: #111827; font-size: 14px;';
                select.parentNode.insertBefore(div, select);
                select.style.display = 'none';
                replacements.push({ original: select, replacement: div });
            });

            // Temporarily remove height constraints for screenshot
            if (parentContainer) {
                parentContainer.style.height = 'auto';
                parentContainer.style.maxHeight = 'none';
                parentContainer.style.overflow = 'visible';
            }

            // Force reflow to ensure styles are applied
            void calculatorRef.current.offsetHeight;

            // Wait for styles to apply and render
            await new Promise(resolve => setTimeout(resolve, 100));

            const canvas = await html2canvas(calculatorRef.current, {
                backgroundColor: '#f3f4f6',
                scale: 2,
                logging: false,
                windowHeight: calculatorRef.current.scrollHeight,
                height: calculatorRef.current.scrollHeight,
                useCORS: true,
                allowTaint: true,
            });

            return canvas.toDataURL('image/png');
        } finally {
            // Remove temporary divs and restore inputs/selects
            replacements.forEach(({ original, replacement, type }) => {
                if (type === 'show') {
                    original.style.display = '';
                } else if (replacement) {
                    replacement.remove();
                    original.style.display = '';
                }
            });

            // Remove screenshot mode class
            calculatorRef.current.classList.remove('screenshot-mode');

            // Restore original styles
            if (parentContainer) {
                parentContainer.style.height = originalHeight || '';
                parentContainer.style.maxHeight = originalMaxHeight || '';
                parentContainer.style.overflow = originalOverflow || '';
            }
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

            <style>{`
                .screenshot-mode .screenshot-hide {
                    display: none !important;
                }
                .screenshot-mode button[aria-label="Decrease"],
                .screenshot-mode button[aria-label="Increase"] {
                    display: none !important;
                }
                .screenshot-mode .flex.items-center {
                    overflow: visible !important;
                }
                .screenshot-mode .flex.items-center.gap-2 {
                    gap: 0 !important;
                }
                .screenshot-mode .relative {
                    overflow: visible !important;
                    display: block !important;
                    position: static !important;
                }
                .screenshot-mode .relative span {
                    position: static !important;
                    display: inline !important;
                    transform: none !important;
                    font-weight: 600 !important;
                    margin-right: 2px !important;
                }
                .screenshot-mode input::before,
                .screenshot-mode input[type="text"]::before {
                    content: attr(value) !important;
                    display: inline !important;
                    font-weight: 600 !important;
                    color: #111827 !important;
                }
                .screenshot-mode input,
                .screenshot-mode input[type="text"] {
                    border: none !important;
                    background: transparent !important;
                    color: #111827 !important;
                    pointer-events: none !important;
                    font-weight: 600 !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    text-align: left !important;
                    width: auto !important;
                    min-width: fit-content !important;
                    max-width: none !important;
                    flex: none !important;
                    display: inline-block !important;
                    overflow: visible !important;
                    white-space: nowrap !important;
                    box-shadow: none !important;
                    outline: none !important;
                    font-size: 14px !important;
                    line-height: 1.5 !important;
                }
                .screenshot-mode select {
                    border: none !important;
                    background: transparent !important;
                    color: #111827 !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    pointer-events: none !important;
                    appearance: none !important;
                    -webkit-appearance: none !important;
                    -moz-appearance: none !important;
                    font-weight: 600 !important;
                    width: auto !important;
                    min-width: fit-content !important;
                    max-width: none !important;
                    display: inline-block !important;
                    overflow: visible !important;
                    box-shadow: none !important;
                    outline: none !important;
                    font-size: 14px !important;
                    line-height: 1.5 !important;
                }
            `}</style>

            <div className="py-4 sm:py-6 bg-gray-100 min-h-screen lg:h-screen lg:overflow-auto">
                <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
                    <div ref={calculatorRef} className="bg-white rounded-lg shadow-lg overflow-hidden">
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
                            <div className="pt-3 border-t screenshot-hide">
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
