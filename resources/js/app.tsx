import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';

type PageModule = {
    default: ComponentType<any>;
};

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob<PageModule>('./Pages/**/*.tsx', { eager: true });
        const page = pages[`./Pages/${name}.tsx`];

        return page?.default ?? page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
