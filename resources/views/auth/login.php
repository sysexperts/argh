<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login - Business Manager</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#008080",
                        "background-light": "#f3f4f6",
                        "background-dark": "#111827",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1f2937",
                        "text-light": "#111827",
                        "text-dark": "#e5e7eb",
                        "subtle-light": "#6b7280",
                        "subtle-dark": "#9ca3af",
                    },
                    fontFamily: {
                        display: ["Poppins", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.5rem",
                        lg: "0.75rem",
                        xl: "1rem",
                    },
                },
            },
        };
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark antialiased">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <img alt="sys-experts Logo" class="mx-auto h-12 w-auto" src="/assets/images/sys-expertslogo.png" onerror="this.style.display='none'"/>
                <h2 class="mt-6 text-2xl font-bold tracking-tight text-text-light dark:text-text-dark">
                    Anmelden
                </h2>
                <p class="mt-2 text-sm text-subtle-light dark:text-subtle-dark">
                    Willkommen zurück zu Ihrem Business Dashboard.
                </p>
            </div>

            <?php if (isset($error)): ?>
            <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                <p class="text-sm"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <div class="bg-surface-light dark:bg-surface-dark p-8 shadow-lg rounded-xl">
                <form action="/auth/login" method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark" for="email">
                            E-Mail oder Benutzername
                        </label>
                        <div class="mt-2">
                            <input
                                autocomplete="email"
                                class="block w-full rounded-lg border-0 py-3 px-4 bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-subtle-light dark:placeholder:text-subtle-dark focus:ring-2 focus:ring-inset focus:ring-primary"
                                id="email"
                                name="email"
                                required=""
                                type="text"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            />
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-text-light dark:text-text-dark" for="password">
                                Passwort
                            </label>
                            <div class="text-sm">
                                <a class="font-semibold text-primary hover:text-opacity-80 transition-colors duration-200" href="/auth/forgot-password">
                                    Passwort vergessen?
                                </a>
                            </div>
                        </div>
                        <div class="mt-2">
                            <input
                                autocomplete="current-password"
                                class="block w-full rounded-lg border-0 py-3 px-4 bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-subtle-light dark:placeholder:text-subtle-dark focus:ring-2 focus:ring-inset focus:ring-primary"
                                id="password"
                                name="password"
                                required=""
                                type="password"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 bg-background-light dark:bg-surface-dark text-primary focus:ring-primary"
                                id="remember-me"
                                name="remember_me"
                                type="checkbox"
                            />
                            <label class="ml-2 block text-sm text-subtle-light dark:text-subtle-dark" for="remember-me">
                                Angemeldet bleiben
                            </label>
                        </div>
                    </div>

                    <div>
                        <button
                            class="flex w-full justify-center rounded-lg bg-primary px-4 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors duration-200"
                            type="submit"
                        >
                            Anmelden
                        </button>
                    </div>
                </form>
            </div>

            <p class="mt-8 text-center text-sm text-subtle-light dark:text-subtle-dark">
                Noch kein Konto?
                <a class="font-semibold leading-6 text-primary hover:text-opacity-80 transition-colors duration-200" href="/auth/register">
                    Zugang anfragen
                </a>
            </p>
        </div>
    </div>
</body>
</html>
