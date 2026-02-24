import React, { useState } from 'react';

export default function ShareModal({ url, onClose }) {
    const [copied, setCopied] = useState(false);

    const handleCopy = async () => {
        try {
            await navigator.clipboard.writeText(url);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (error) {
            console.error('Failed to copy:', error);
        }
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <div className="p-6">
                    <div className="flex justify-between items-start mb-4">
                        <h3 className="text-xl font-bold text-gray-900">
                            Share Your Calculator
                        </h3>
                        <button
                            onClick={onClose}
                            className="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg
                                className="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <p className="text-gray-600 mb-4">
                        Your calculator screenshot has been saved. Share the link below:
                    </p>

                    <div className="bg-gray-50 p-3 rounded-lg border border-gray-200 mb-4">
                        <div className="flex items-center gap-2">
                            <input
                                type="text"
                                value={url}
                                readOnly
                                className="flex-1 bg-transparent border-none focus:outline-none text-sm text-gray-700 select-all"
                            />
                            <button
                                onClick={handleCopy}
                                className="px-4 py-2 bg-[#d32f2f] hover:bg-[#b71c1c] text-white text-sm font-semibold rounded transition-colors"
                            >
                                {copied ? 'Copied!' : 'Copy'}
                            </button>
                        </div>
                    </div>

                    <div className="flex justify-end">
                        <button
                            onClick={onClose}
                            className="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
