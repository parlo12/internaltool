import React from 'react';

interface FinancialInputProps {
    label: string;
    value: number;
    onChange: (value: number) => void;
    type: 'currency' | 'percent';
    step?: number;
    displayAmount?: number;
}

export default function FinancialInput({
    label,
    value,
    onChange,
    type,
    step = 1,
    displayAmount
}: FinancialInputProps) {
    const handleIncrement = () => {
        onChange(value + step);
    };

    const handleDecrement = () => {
        const newValue = value - step;
        onChange(newValue < 0 ? 0 : newValue);
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const val = e.target.value.replace(/[^0-9.]/g, '');
        const numVal = parseFloat(val);
        onChange(isNaN(numVal) ? 0 : numVal);
    };

    const formatValue = (val: number): string => {
        if (type === 'currency') {
            return val.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
        }
        return val.toString();
    };

    const formatDisplayAmount = (amount: number): string => {
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
                        <span className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 font-medium">
                            $
                        </span>
                    )}
                    <input
                        type="text"
                        value={formatValue(value)}
                        onChange={handleInputChange}
                        className={`w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-right font-semibold ${
                            type === 'currency' ? 'pl-8' : ''
                        }`}
                    />
                    {type === 'percent' && (
                        <span className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 font-medium">
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
