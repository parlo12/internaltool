import React, { useState, useRef } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import FinancialInput from '@/Components/Calculator/FinancialInput';
import { useCalculator } from '@/Hooks/useCalculator';
import ShareModal from '@/Components/Calculator/ShareModal';
import html2canvas from 'html2canvas';
import axios from 'axios';

export default function PropertyCalculator({ auth }) {
    const calculatorRef = useRef(null);
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

    const captureScreenshot = async () => {
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

    const handleShareViaWhatsApp = async () => {
        try {
            setIsGeneratingLink(true);
            const dataUrl = await captureScreenshot();

            const response = await axios.post('/api/calculator/share', {
                image: dataUrl,
            });

            if (response.data.success) {
                const text = `Check out my property investment calculator results! ${response.data.url}`;
                const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
                window.open(whatsappUrl, '_blank');
            }
        } catch (error) {
            console.error('Error sharing via WhatsApp:', error);
            alert('Failed to share via WhatsApp');
        } finally {
            setIsGeneratingLink(false);
        }
    };

    const handleShareViaFacebook = async () => {
        try {
            setIsGeneratingLink(true);
            const dataUrl = await captureScreenshot();

            const response = await axios.post('/api/calculator/share', {
                image: dataUrl,
            });

            if (response.data.success) {
                const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(response.data.url)}`;
                window.open(facebookUrl, '_blank', 'width=600,height=400');
            }
        } catch (error) {
            console.error('Error sharing via Facebook:', error);
            alert('Failed to share via Facebook');
        } finally {
            setIsGeneratingLink(false);
        }
    };

    const handleShareViaTwitter = async () => {
        try {
            setIsGeneratingLink(true);
            const dataUrl = await captureScreenshot();

            const response = await axios.post('/api/calculator/share', {
                image: dataUrl,
            });

            if (response.data.success) {
                const text = 'Check out my property investment calculator results!';
                const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(response.data.url)}`;
                window.open(twitterUrl, '_blank', 'width=600,height=400');
            }
        } catch (error) {
            console.error('Error sharing via Twitter:', error);
            alert('Failed to share via Twitter');
        } finally {
            setIsGeneratingLink(false);
        }
    };

    const handleShareViaWebShare = async () => {
        try {
            if (!navigator.share) {
                alert('Web Share API is not supported in your browser. Please use individual share buttons.');
                return;
            }

            setIsGeneratingLink(true);
            const dataUrl = await captureScreenshot();

            const response = await axios.post('/api/calculator/share', {
                image: dataUrl,
            });

            if (response.data.success) {
                await navigator.share({
                    title: 'Property Investment Calculator',
                    text: 'Check out my property investment calculator results!',
                    url: response.data.url,
                });
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

            <div className="py-12 bg-gray-100 min-h-screen">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div ref={calculatorRef} className="bg-white rounded-lg shadow-lg overflow-hidden">
                        {/* Red Summary Header */}
                        <div className="bg-[#d32f2f] text-white p-6">
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
                            <div className="space-y-4 pt-4 border-t">
                                {/* Download & Copy Link */}
                                <div>
                                    <h4 className="text-sm font-semibold text-gray-700 mb-3">Download & Copy</h4>
                                    <div className="flex flex-wrap gap-3">
                                        <button
                                            onClick={handleDownloadScreenshot}
                                            className="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors flex items-center gap-2"
                                        >
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Download Screenshot
                                        </button>
                                        <button
                                            onClick={handleGenerateShareLink}
                                            disabled={isGeneratingLink}
                                            className="px-6 py-3 bg-[#d32f2f] hover:bg-[#b71c1c] text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        >
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            {isGeneratingLink ? 'Generating...' : 'Copy Share Link'}
                                        </button>
                                    </div>
                                </div>

                                {/* Social Media Share */}
                                <div>
                                    <h4 className="text-sm font-semibold text-gray-700 mb-3">Share on Social Media</h4>
                                    <div className="flex flex-wrap gap-3">
                                        <button
                                            onClick={handleShareViaWhatsApp}
                                            disabled={isGeneratingLink}
                                            className="px-6 py-3 bg-[#25D366] hover:bg-[#1da851] text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        >
                                            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                            </svg>
                                            WhatsApp
                                        </button>
                                        <button
                                            onClick={handleShareViaFacebook}
                                            disabled={isGeneratingLink}
                                            className="px-6 py-3 bg-[#1877F2] hover:bg-[#0d66d0] text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        >
                                            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                            Facebook
                                        </button>
                                        <button
                                            onClick={handleShareViaTwitter}
                                            disabled={isGeneratingLink}
                                            className="px-6 py-3 bg-[#1DA1F2] hover:bg-[#0d8bd9] text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        >
                                            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                            </svg>
                                            Twitter
                                        </button>
                                        {navigator.share && (
                                            <button
                                                onClick={handleShareViaWebShare}
                                                disabled={isGeneratingLink}
                                                className="px-6 py-3 bg-gray-800 hover:bg-gray-900 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                            >
                                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                </svg>
                                                More Options
                                            </button>
                                        )}
                                    </div>
                                </div>
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
