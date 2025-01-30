import React, { useState } from "react";

const Tooltip = ({ text, children }) => {
    const [visible, setVisible] = useState(false);

    return (
        <div
            className="relative flex items-center"
            onMouseEnter={() => setVisible(true)}
            onMouseLeave={() => setVisible(false)}
        >
            {children}
            {visible && (
                <div className="absolute bottom-full mb-2 w-96 p-2 bg-gray-700 text-white text-xs rounded shadow-lg z-10">
                    {text}
                </div>
            )}
        </div>
    );
};

export default Tooltip;
