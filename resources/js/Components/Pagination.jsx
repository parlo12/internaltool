import { Link } from "@inertiajs/react";

export default function Pagination({ links, queryParams }) {
    const appendQueryParams = (url, params) => {
        if (!url) return "";
        if (!params || Object.keys(params).length === 0) return url;

        const urlObj = new URL(url, window.location.origin);
        Object.keys(params).forEach(key => {
            if (key !== 'page') {
                urlObj.searchParams.set(key, params[key]);
            }
        });

        return urlObj.toString();
    };

    return (
        <nav className="text-center mt-4">
            {links.map((link) => {
                const mergedUrl = appendQueryParams(link.url, queryParams);
                return (
                    <Link
                        preserveScroll
                        href={mergedUrl}
                        key={link.label}
                        className={
                            "inline-block py-2 px-3 rounded-lg text-gray-700 text-xs " +
                            (link.active ? "bg-gray-300 " : " ") +
                            (!link.url
                                ? "!text-gray-500 cursor-not-allowed "
                                : "hover:bg-gray-950 hover:text-white ")
                        }
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    ></Link>
                );
            })}
        </nav>
    );
}
