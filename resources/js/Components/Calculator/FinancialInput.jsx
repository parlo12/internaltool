import React from 'react';

export default function FinancialInput({
    label,
    value,
    onChange,
    type,
    step = 1,
    displayAmount
}) {
    const handleIncrement = () => {
        onChange(value + step);
    };

    const handleDecrement = () => {
        const newValue = value - step;
        onChange(newValue < 0 ? 0 : newValue);
    };

    const handleInputChange = (e) => {
        const val = e.target.value.replace(/[^0-9.]/g, '');
        const numVal = parseFloat(val);
        onChange(isNaN(numVal) ? 0 : numVal);
    };

    const formatValue = (val) => {
        if (type === 'currency') {
            return val.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
        }
        return val.toString();
    };

    const formatDisplayAmount = (amount) => {
        return amount.toLocaleString('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });
    };

    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
                {label}
                {displayAmount !== undefined && (
                    <span className="ml-2 text-gray-500 font-normal">
                        ({formatDisplayAmount(displayAmount)})
                    </span>
                )}
            </label>
            <div className="flex items-center gap-2">
                <button
                    type="button"
                    onClick={handleDecrement}
                    className="w-10 h-10 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-lg font-bold text-gray-700 transition-colors"
                    aria-label="Decrease"
                >
                    -
                </button>
                <div className="relative flex-1">
                    {type === 'currency' && (
                        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 font-medium">
                            $
                        </span>
                    )}
                    <input
                        type="text"
                        value={formatValue(value)}
                        onChange={handleInputChange}
                        className={`w-full py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 font-semibold ${
                            type === 'currency' ? 'pl-7 pr-4 text-left' : 'pl-4 pr-8 text-right'
                        }`}
                    />
                    {type === 'percent' && (
                        <span className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 font-medium">
                            %
                        </span>
                    )}
                </div>
                <button
                    type="button"
                    onClick={handleIncrement}
                    className="w-10 h-10 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-lg font-bold text-gray-700 transition-colors"
                    aria-label="Increase"
                >
                    +
                </button>
            </div>
        </div>
    );
}
