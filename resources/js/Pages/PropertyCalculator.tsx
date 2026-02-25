import React, { useState, useRef } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import FinancialInput from '@/Components/Calculator/FinancialInput';
import { useCalculator } from '@/Hooks/useCalculator';
import ShareModal from '@/Components/Calculator/ShareModal';
import html2canvas from 'html2canvas';
import axios from 'axios';

export default function PropertyCalculator({ auth }: any) {
    const calculatorRef = useRef<HTMLDivElement>(null);
    const [showShareModal, setShowShareModal] = useState(false);
    const [shareUrl, setShareUrl] = useState('');
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

    const captureScreenshot = async (): Promise<string> => {
        if (!calculatorRef.current) throw new Error('Calculator ref not found');

        const canvas = await html2canvas(calculatorRef.current, {
            backgroundColor: '#f3f4f6',
            scale: 2,
            logging: false,
        });

        return canvas.toDataURL('image/png');
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

    const handleGenerateShareLink = async () => {
        try {
            setIsGeneratingLink(true);
            const dataUrl = await captureScreenshot();

            const response = await axios.post('/api/calculator/share', {
                image: dataUrl,
            });

            if (response.data.success) {
                setShareUrl(response.data.url);
                setShowShareModal(true);
            }
        } catch (error) {
            console.error('Error generating share link:', error);
            alert('Failed to generate share link');
        } finally {
            setIsGeneratingLink(false);
        }
    };

    const formatCurrency = (value: number): string => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    };

    const formatPercent = (value: number): string => {
        return `${value.toFixed(2)}%`;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Property Investment Calculator</h2>}
        >
            <Head title="Property Calculator" />

            <div className="py-12 bg-gray-100 min-h-screen">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div ref={calculatorRef} className="bg-white rounded-lg shadow-lg overflow-hidden">
                        {/* Summary Header */}
                        <div className={`${calculations.monthlyCashflow >= 0 ? 'bg-[#2e7d32]' : 'bg-[#d32f2f]'} text-white p-6 transition-colors duration-300`}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Left Section */}
                                <div>
                                    <div className="mb-4">
                                        <div className="text-sm opacity-90 mb-1">Cash on Cash</div>
                                        <div className="text-4xl font-bold">
                                            {formatPercent(calculations.cashOnCash)}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-sm opacity-90 mb-1">Total ROI</div>
                                        <div className="text-4xl font-bold">
                                            {formatPercent(calculations.totalROI)}
                                        </div>
                                    </div>
                                </div>

                                {/* Right Section */}
                                <div>
                                    <div className="mb-4">
                                        <div className="text-sm opacity-90 mb-1">Monthly Cashflow</div>
                                        <div className="text-4xl font-bold">
                                            {formatCurrency(calculations.monthlyCashflow)}
                                            <span className="text-lg ml-2">
                                                ({formatCurrency(calculations.annualCashflow)})
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-sm opacity-90 mb-1">Cap Rate</div>
                                        <div className="text-4xl font-bold">
                                            {formatPercent(calculations.capRate)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Calculator Body */}
                        <div className="p-6 space-y-6">
                            {/* Purchase Details */}
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                                    Purchase Details
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <FinancialInput
                                        label="Price"
                                        value={inputs.price}
                                        onChange={(val) => updateInput('price', val)}
                                        type="currency"
                                    />
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
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Loan Term
                                        </label>
                                        <select
                                            value={inputs.loanTermYears}
                                            onChange={(e) => updateInput('loanTermYears', Number(e.target.value))}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                        >
                                            {loanTermOptions.map(option => (
                                                <option key={option.value} value={option.value}>
                                                    {option.label}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <FinancialInput
                                        label="Closing Costs"
                                        value={inputs.closingCosts}
                                        onChange={(val) => updateInput('closingCosts', val)}
                                        type="currency"
                                    />
                                    <FinancialInput
                                        label="Rehab"
                                        value={inputs.rehab}
                                        onChange={(val) => updateInput('rehab', val)}
                                        type="currency"
                                    />
                                </div>
                            </div>

                            {/* Income */}
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                                    Income
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <FinancialInput
                                        label="Monthly Rental Income"
                                        value={inputs.monthlyRent}
                                        onChange={(val) => updateInput('monthlyRent', val)}
                                        type="currency"
                                    />
                                </div>
                            </div>

                            {/* Monthly Expenses */}
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                                    Monthly Expenses
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="bg-gray-50 p-3 rounded-lg">
                                        <div className="text-sm font-medium text-gray-700 mb-1">Mortgage Payment</div>
                                        <div className="text-2xl font-bold text-gray-900">
                                            {formatCurrency(calculations.monthlyMortgage)}
                                        </div>
                                    </div>
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
                                </div>
                            </div>

                            {/* Percentage-based Expenses */}
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                                    Additional Expenses (% of Rent)
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                            {/* Share Buttons */}
                            <div className="flex flex-wrap gap-3 pt-4 border-t">
                                <button
                                    onClick={handleDownloadScreenshot}
                                    className="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors"
                                >
                                    Download Screenshot
                                </button>
                                <button
                                    onClick={handleGenerateShareLink}
                                    disabled={isGeneratingLink}
                                    className="px-6 py-3 bg-[#d32f2f] hover:bg-[#b71c1c] text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {isGeneratingLink ? 'Generating...' : 'Generate Share Link'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {showShareModal && (
                <ShareModal
                    url={shareUrl}
                    onClose={() => setShowShareModal(false)}
                />
            )}
        </AuthenticatedLayout>
    );
}
