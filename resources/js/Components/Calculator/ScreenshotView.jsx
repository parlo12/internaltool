import React, { forwardRef } from 'react';

const ScreenshotView = forwardRef(({ inputs, calculations, getExpenseDollarAmount, formatCurrency, formatPercent }, ref) => {
    return (
        <div 
            ref={ref} 
            className="bg-white rounded-lg shadow-lg overflow-hidden w-[800px]"
            style={{ position: 'absolute', left: '-9999px', top: 0 }}
        >
            {/* Summary Header */}
            <div className={`${calculations.monthlyCashflow >= 0 ? 'bg-[#2e7d32]' : 'bg-[#d32f2f]'} text-white p-6`}>
                <div className="grid grid-cols-2 gap-6">
                    <div>
                        <div className="text-sm opacity-90 mb-1">Cash on Cash</div>
                        <div className="text-4xl font-bold leading-tight">
                            {formatPercent(calculations.cashOnCash)}
                        </div>
                    </div>
                    <div>
                        <div className="text-sm opacity-90 mb-1">Total ROI</div>
                        <div className="text-4xl font-bold leading-tight">
                            {formatPercent(calculations.totalROI)}
                        </div>
                    </div>
                    <div>
                        <div className="text-sm opacity-90 mb-1">Monthly Cashflow</div>
                        <div className="text-4xl font-bold leading-tight">
                            {formatCurrency(calculations.monthlyCashflow)}
                        </div>
                        <div className="text-sm opacity-75 mt-1">
                            ({formatCurrency(calculations.annualCashflow)}/yr)
                        </div>
                    </div>
                    <div>
                        <div className="text-sm opacity-90 mb-1">Cap Rate</div>
                        <div className="text-4xl font-bold leading-tight">
                            {formatPercent(calculations.capRate)}
                        </div>
                    </div>
                </div>
            </div>

            {/* Calculator Body */}
            <div className="p-6 space-y-6">
                {/* Purchase Details & Income Combined */}
                <div className="grid grid-cols-2 gap-8">
                    {/* Purchase Details */}
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                            Purchase Details
                        </h3>
                        <div className="space-y-4">
                            <div>
                                <div className="text-sm font-medium text-gray-700 mb-1">Price</div>
                                <div className="text-base font-semibold text-gray-900">{formatCurrency(inputs.price)}</div>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <div className="text-sm font-medium text-gray-700 mb-1">Down Payment</div>
                                    <div className="text-base font-semibold text-gray-900">{inputs.downPaymentPercent}%</div>
                                </div>
                                <div>
                                    <div className="text-sm font-medium text-gray-700 mb-1">Interest Rate</div>
                                    <div className="text-base font-semibold text-gray-900">{inputs.interestRate}%</div>
                                </div>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <div>
                                    <div className="text-sm font-medium text-gray-700 mb-1">Loan Term</div>
                                    <div className="text-base font-semibold text-gray-900">{inputs.loanTermYears} years</div>
                                </div>
                                <div className="col-span-2">
                                    <div className="text-sm font-medium text-gray-700 mb-1">Closing Costs</div>
                                    <div className="text-base font-semibold text-gray-900">{formatCurrency(inputs.closingCosts)}</div>
                                </div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-gray-700 mb-1">Rehab</div>
                                <div className="text-base font-semibold text-gray-900">{formatCurrency(inputs.rehab)}</div>
                            </div>
                        </div>
                    </div>

                    {/* Income & Mortgage */}
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                            Income
                        </h3>
                        <div className="space-y-4">
                            <div>
                                <div className="text-sm font-medium text-gray-700 mb-1">Monthly Rental Income</div>
                                <div className="text-base font-semibold text-gray-900">{formatCurrency(inputs.monthlyRent)}</div>
                            </div>
                            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200 mt-4">
                                <div className="text-sm font-medium text-gray-600 mb-1">Mortgage Payment</div>
                                <div className="text-xl font-bold text-gray-900">
                                    {formatCurrency(calculations.monthlyMortgage)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Monthly Expenses */}
                <div>
                    <h3 className="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                        Monthly Expenses
                    </h3>
                    <div className="grid grid-cols-3 gap-6">
                        <div>
                            <div className="text-sm font-medium text-gray-700 mb-1">Property Taxes</div>
                            <div className="text-base font-semibold text-gray-900">{formatCurrency(inputs.taxes)}</div>
                        </div>
                        <div>
                            <div className="text-sm font-medium text-gray-700 mb-1">Insurance</div>
                            <div className="text-base font-semibold text-gray-900">{formatCurrency(inputs.insurance)}</div>
                        </div>
                        <div>
                            <div className="text-sm font-medium text-gray-700 mb-1">Utilities</div>
                            <div className="text-base font-semibold text-gray-900">{formatCurrency(inputs.utilities)}</div>
                        </div>
                        <div>
                            <div className="text-sm font-medium text-gray-700 mb-1">Maintenance</div>
                            <div className="text-base font-semibold text-gray-900">{formatCurrency(inputs.maintenance)}</div>
                        </div>
                        <div>
                            <div className="text-sm font-medium text-gray-700 mb-1">Miscellaneous</div>
                            <div className="text-base font-semibold text-gray-900">{formatCurrency(inputs.miscellaneous)}</div>
                        </div>
                        <div>
                            <div className="text-sm font-medium text-gray-700 mb-1">Capital Expenditure <span className="text-gray-500 font-normal">({formatCurrency(getExpenseDollarAmount(inputs.capExPercent))})</span></div>
                            <div className="text-base font-semibold text-gray-900">{inputs.capExPercent}%</div>
                        </div>
                        <div>
                            <div className="text-sm font-medium text-gray-700 mb-1">Property Management <span className="text-gray-500 font-normal">({formatCurrency(getExpenseDollarAmount(inputs.propertyManagementPercent))})</span></div>
                            <div className="text-base font-semibold text-gray-900">{inputs.propertyManagementPercent}%</div>
                        </div>
                        <div>
                            <div className="text-sm font-medium text-gray-700 mb-1">Vacancy <span className="text-gray-500 font-normal">({formatCurrency(getExpenseDollarAmount(inputs.vacancyPercent))})</span></div>
                            <div className="text-base font-semibold text-gray-900">{inputs.vacancyPercent}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
});

export default ScreenshotView;
