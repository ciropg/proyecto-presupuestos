<button
    type="button"
    x-data
    @click="$store.theme.toggle()"
    x-bind:aria-label="$store.theme.isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
    x-bind:title="$store.theme.isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
    class="fixed bottom-4 right-4 z-50 inline-flex h-12 w-12 items-center justify-center rounded-full border border-gray-200 bg-white/95 text-gray-700 shadow-lg backdrop-blur transition duration-200 hover:-translate-y-0.5 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:border-slate-700 dark:bg-slate-900/95 dark:text-slate-100 dark:hover:bg-slate-800 dark:focus-visible:ring-offset-slate-950 sm:bottom-6 sm:right-6"
>
    <span class="sr-only" x-text="$store.theme.isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'">
        Cambiar tema
    </span>

    <svg
        x-show="!$store.theme.isDark"
        class="h-5 w-5"
        viewBox="0 0 20 20"
        fill="currentColor"
        aria-hidden="true"
    >
        <path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75V4a.75.75 0 0 1-1.5 0V2.75A.75.75 0 0 1 10 2Zm0 11.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM10 15.25a.75.75 0 0 1 .75.75V17.25a.75.75 0 0 1-1.5 0V16a.75.75 0 0 1 .75-.75ZM4 9.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H4Zm13.25 0a.75.75 0 0 1 0 1.5H16a.75.75 0 0 1 0-1.5h1.25ZM5.47 4.47a.75.75 0 0 1 1.06 0l.884.884a.75.75 0 1 1-1.06 1.06L5.47 5.53a.75.75 0 0 1 0-1.06Zm8.116 8.116a.75.75 0 0 1 1.06 0l.884.884a.75.75 0 1 1-1.06 1.06l-.884-.884a.75.75 0 0 1 0-1.06ZM15.53 4.47a.75.75 0 0 1 0 1.06l-.884.884a.75.75 0 0 1-1.06-1.06l.884-.884a.75.75 0 0 1 1.06 0ZM7.414 13.586a.75.75 0 0 1 0 1.06l-.884.884a.75.75 0 1 1-1.06-1.06l.884-.884a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
    </svg>

    <svg
        x-cloak
        x-show="$store.theme.isDark"
        class="h-5 w-5"
        viewBox="0 0 20 20"
        fill="currentColor"
        aria-hidden="true"
    >
        <path d="M17.293 13.293A8 8 0 0 1 6.707 2.707a8.002 8.002 0 1 0 10.586 10.586Z" />
    </svg>
</button>
