<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';

type SampleReply = { id: number; source: string };
type ParaphraseResult = {
    id: number;
    source: string;
    rewritten: string;
    kept_tokens: string[];
    valid: boolean;
    mode: string;
    notice: string | null;
    status: string;
};

const props = defineProps<{ samples: SampleReply[] }>();

const source = ref(props.samples[0]?.source ?? '');
const result = ref<ParaphraseResult | null>(null);
const loading = ref(false);
const error = ref('');
const copied = ref(false);
const characterCount = computed(() => source.value.length);
const canSubmit = computed(
    () => source.value.trim().length >= 12 && !loading.value,
);

function chooseSample(sample: SampleReply) {
    source.value = sample.source;
    result.value = null;
    error.value = '';
}

function clearDraft() {
    source.value = '';
    result.value = null;
    error.value = '';
}

async function paraphrase() {
    if (!canSubmit.value) return;

    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/paraphrases', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document.querySelector<HTMLMetaElement>(
                        'meta[name="csrf-token"]',
                    )?.content ?? '',
            },
            body: JSON.stringify({ source: source.value }),
        });
        const payload = await response.json();

        if (!response.ok) {
            error.value =
                payload.message ??
                'We could not process this reply. Check the text and try again.';
            return;
        }

        result.value = payload as ParaphraseResult;
    } catch {
        error.value = 'The desk could not be reached. Please try again.';
    } finally {
        loading.value = false;
    }
}

async function copyResult() {
    if (!result.value) return;

    await navigator.clipboard.writeText(result.value.rewritten);
    copied.value = true;
    window.setTimeout(() => (copied.value = false), 1600);
}

function tokenLabel(token: string) {
    if (token.startsWith('HD-')) return 'Order ID';
    if (token.includes('@')) return 'Email';
    return 'Number';
}
</script>

<template>
    <Head title="Paraphrase Desk">
        <meta
            name="description"
            content="Rewrite Harbor Desk support replies while keeping order IDs, emails, and numbers intact."
        />
    </Head>

    <div class="desk-shell min-h-screen text-slate-900">
        <header class="border-b border-slate-200/80 bg-white/85">
            <div
                class="mx-auto flex h-[72px] max-w-[1440px] items-center justify-between px-5 sm:px-8"
            >
                <a
                    href="/"
                    class="flex items-center gap-3"
                    aria-label="Paraphrase Desk home"
                >
                    <span
                        class="brand-mark flex h-10 w-10 items-center justify-center rounded-xl text-white shadow-sm"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="h-[22px] w-[22px]"
                            aria-hidden="true"
                        >
                            <path
                                d="M5 7.5A2.5 2.5 0 0 1 7.5 5h9A2.5 2.5 0 0 1 19 7.5v5a2.5 2.5 0 0 1-2.5 2.5H11l-4.5 4v-4.22A2.5 2.5 0 0 1 5 12.5v-5Z"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M9 9h6M9 12h3"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                    <span>
                        <span
                            class="block text-[15px] font-semibold tracking-[-0.025em] text-slate-900"
                            >Paraphrase Desk</span
                        >
                        <span
                            class="mt-0.5 block text-[11px] font-medium tracking-[0.08em] text-slate-500 uppercase"
                            >Harbor Desk · Support tools</span
                        >
                    </span>
                </a>

                <div class="flex items-center gap-3">
                    <span
                        class="hidden items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-800 sm:inline-flex"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                        ></span>
                        Token guard active
                    </span>
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-[#e8e8e0] text-xs font-semibold text-slate-700"
                        aria-label="Guest workspace"
                        >G</span
                    >
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-[1440px] px-5 pt-9 pb-12 sm:px-8 sm:pt-12">
            <section
                class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end"
            >
                <div>
                    <div
                        class="mb-3 inline-flex items-center gap-2 text-[11px] font-semibold tracking-[0.16em] text-[#8b6b45] uppercase"
                    >
                        <span class="h-px w-6 bg-[#c19a68]"></span>
                        Reply studio
                    </div>
                    <h1
                        class="max-w-2xl text-[32px] leading-[1.12] font-semibold tracking-[-0.045em] text-slate-900 sm:text-[40px]"
                    >
                        Say it better. Keep every detail.
                    </h1>
                    <p
                        class="mt-3 max-w-2xl text-[15px] leading-6 text-slate-600"
                    >
                        Polish a support reply in seconds. Order IDs, email
                        addresses, and numbers are checked before you copy.
                    </p>
                </div>
                <div
                    class="flex items-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-[0_2px_8px_rgba(24,36,51,0.035)] md:self-auto"
                >
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#f1eee8] text-[#876843]"
                    >
                        <svg
                            viewBox="0 0 20 20"
                            fill="none"
                            class="h-4 w-4"
                            aria-hidden="true"
                        >
                            <path
                                d="M10 2.5v2M10 15.5v2M17.5 10h-2M4.5 10h-2m12.8-5.3-1.4 1.4M6.1 13.9l-1.4 1.4m10.6 0-1.4-1.4M6.1 6.1 4.7 4.7"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                            <circle
                                cx="10"
                                cy="10"
                                r="3.25"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                        </svg>
                    </span>
                    <span class="text-xs font-medium text-slate-600"
                        >Private by design
                        <span class="text-slate-400">·</span> Guest
                        workspace</span
                    >
                </div>
            </section>

            <section v-if="samples.length" class="mb-5">
                <div class="mb-2.5 flex items-center justify-between">
                    <p
                        class="text-[11px] font-semibold tracking-[0.12em] text-slate-500 uppercase"
                    >
                        Start with a sample
                    </p>
                    <p class="text-xs text-slate-400">
                        Fictional support replies
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="(sample, index) in samples"
                        :key="sample.id"
                        type="button"
                        class="sample-chip inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:border-[#c8b18f] hover:bg-[#fcfaf7] hover:text-slate-900"
                        @click="chooseSample(sample)"
                    >
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-md bg-slate-100 text-[10px] font-bold text-slate-500"
                            >0{{ index + 1 }}</span
                        >
                        <span>{{
                            sample.source.match(/HD-\d+/)?.[0] ??
                            `Reply ${index + 1}`
                        }}</span>
                    </button>
                </div>
            </section>

            <div
                class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_260px]"
            >
                <section
                    class="panel-card overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-[0_6px_24px_rgba(25,38,52,0.045)]"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6"
                    >
                        <div class="flex items-center gap-3">
                            <span class="step-index">01</span>
                            <div>
                                <h2
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Original reply
                                </h2>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    Paste the message you want to polish
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="quiet-button"
                            :disabled="!source"
                            @click="clearDraft"
                        >
                            Clear
                        </button>
                    </div>

                    <div class="p-5 sm:p-6">
                        <label for="source" class="sr-only"
                            >Original support reply</label
                        >
                        <textarea
                            id="source"
                            v-model="source"
                            maxlength="5000"
                            rows="11"
                            placeholder="Paste a Harbor Desk support reply here…"
                            class="reply-input block min-h-[250px] w-full resize-y rounded-xl border border-slate-200 bg-[#fcfcfb] px-4 py-4 text-[14px] leading-6 text-slate-800 transition outline-none placeholder:text-slate-400 focus:border-[#a78963] focus:bg-white focus:ring-4 focus:ring-[#c9aa7d]/15"
                        ></textarea>

                        <div
                            class="mt-3 flex items-center justify-between gap-3"
                        >
                            <p
                                class="flex items-center gap-1.5 text-xs text-slate-500"
                            >
                                <svg
                                    viewBox="0 0 16 16"
                                    fill="none"
                                    class="h-4 w-4 text-[#9b805c]"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M8 1.5 13 3.4v3.8c0 3.2-2.1 5.5-5 7.3-2.9-1.8-5-4.1-5-7.3V3.4l5-1.9Z"
                                        stroke="currentColor"
                                        stroke-width="1.3"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="m5.8 7.8 1.5 1.5 3-3"
                                        stroke="currentColor"
                                        stroke-width="1.3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                Protected tokens are never intentionally
                                rewritten
                            </p>
                            <span
                                class="shrink-0 text-xs text-slate-400 tabular-nums"
                                >{{ characterCount }} / 5,000</span
                            >
                        </div>

                        <p
                            v-if="error"
                            role="alert"
                            class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3.5 py-3 text-sm text-rose-800"
                        >
                            {{ error }}
                        </p>

                        <button
                            type="button"
                            class="rewrite-button mt-5 flex w-full items-center justify-center gap-2.5 rounded-xl px-4 py-3.5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-55"
                            :disabled="!canSubmit"
                            @click="paraphrase"
                        >
                            <svg
                                v-if="loading"
                                class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                    stroke="currentColor"
                                    stroke-opacity=".25"
                                    stroke-width="3"
                                />
                                <path
                                    d="M21 12a9 9 0 0 0-9-9"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <svg
                                v-else
                                viewBox="0 0 20 20"
                                fill="none"
                                class="h-[17px] w-[17px]"
                                aria-hidden="true"
                            >
                                <path
                                    d="m11.7 2.5 1.1 3.2 3.2 1.1-3.2 1.1-1.1 3.2-1.1-3.2-3.2-1.1 3.2-1.1 1.1-3.2ZM5 11.8l.8 2.4 2.4.8-2.4.8L5 18.2l-.8-2.4-2.4-.8 2.4-.8.8-2.4Z"
                                    stroke="currentColor"
                                    stroke-width="1.35"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            {{ loading ? 'Polishing reply…' : 'Rewrite reply' }}
                            <span v-if="!loading" class="ml-1 text-white/60"
                                >↗</span
                            >
                        </button>
                        <p class="mt-3 text-center text-[11px] text-slate-400">
                            {{
                                source.trim().length < 12
                                    ? 'Enter at least 12 characters to continue'
                                    : 'Review the result before sending to a customer'
                            }}
                        </p>
                    </div>
                </section>

                <section
                    class="panel-card overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-[0_6px_24px_rgba(25,38,52,0.045)]"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6"
                    >
                        <div class="flex items-center gap-3">
                            <span class="step-index step-index-result">02</span>
                            <div>
                                <h2
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Polished reply
                                </h2>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    Ready for a human review
                                </p>
                            </div>
                        </div>
                        <span
                            v-if="result"
                            :class="
                                result.valid ? 'status-valid' : 'status-invalid'
                            "
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-semibold tracking-wide uppercase"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="
                                    result.valid
                                        ? 'bg-emerald-500'
                                        : 'bg-rose-500'
                                "
                            ></span>
                            {{
                                result.valid
                                    ? 'Tokens verified'
                                    : 'Review needed'
                            }}
                        </span>
                        <span
                            v-else
                            class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold tracking-wide text-slate-400 uppercase"
                            >Awaiting rewrite</span
                        >
                    </div>

                    <div class="flex min-h-[411px] flex-col p-5 sm:p-6">
                        <template v-if="result">
                            <div
                                v-if="!result.valid"
                                class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-3 text-xs leading-5 text-amber-900"
                            >
                                {{ result.notice }}
                            </div>
                            <p
                                class="text-[14px] leading-[1.85] whitespace-pre-wrap text-slate-800"
                            >
                                {{ result.rewritten }}
                            </p>
                            <div class="mt-auto pt-8">
                                <div
                                    v-if="result.valid"
                                    class="mb-4 flex items-start gap-2.5 rounded-lg bg-emerald-50/80 px-3.5 py-3 text-xs leading-5 text-emerald-900"
                                >
                                    <svg
                                        viewBox="0 0 16 16"
                                        fill="none"
                                        class="mt-0.5 h-4 w-4 shrink-0"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="8"
                                            cy="8"
                                            r="6.2"
                                            stroke="currentColor"
                                            stroke-width="1.3"
                                        />
                                        <path
                                            d="m5.2 8.1 1.8 1.8 3.8-4"
                                            stroke="currentColor"
                                            stroke-width="1.3"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    <span>{{
                                        result.notice ??
                                        'Every protected token matches the original reply.'
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-3 border-t border-slate-100 pt-4"
                                >
                                    <span class="text-[11px] text-slate-400"
                                        >Rewritten with {{ result.mode }}</span
                                    >
                                    <button
                                        type="button"
                                        class="copy-button inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                        @click="copyResult"
                                    >
                                        <svg
                                            v-if="!copied"
                                            viewBox="0 0 16 16"
                                            fill="none"
                                            class="h-3.5 w-3.5"
                                            aria-hidden="true"
                                        >
                                            <rect
                                                x="5.3"
                                                y="5.3"
                                                width="8.2"
                                                height="8.2"
                                                rx="1.5"
                                                stroke="currentColor"
                                                stroke-width="1.2"
                                            />
                                            <path
                                                d="M10.7 5V3.8A1.8 1.8 0 0 0 8.9 2H3.8A1.8 1.8 0 0 0 2 3.8v5.1a1.8 1.8 0 0 0 1.8 1.8H5"
                                                stroke="currentColor"
                                                stroke-width="1.2"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            viewBox="0 0 16 16"
                                            fill="none"
                                            class="h-3.5 w-3.5 text-emerald-700"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="m3 8.3 3.2 3.1L13 4.8"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                        {{ copied ? 'Copied' : 'Copy reply' }}
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div
                            v-else
                            class="flex flex-1 flex-col items-center justify-center px-4 py-12 text-center"
                        >
                            <div
                                class="relative mb-5 flex h-[66px] w-[66px] items-center justify-center rounded-[20px] border border-slate-200 bg-[#f9f9f7] text-slate-300"
                            >
                                <svg
                                    viewBox="0 0 32 32"
                                    fill="none"
                                    class="h-8 w-8"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M8 8.5A3.5 3.5 0 0 1 11.5 5h12A3.5 3.5 0 0 1 27 8.5v9a3.5 3.5 0 0 1-3.5 3.5h-7l-5.5 5v-5.2A3.5 3.5 0 0 1 8 17.5v-9Z"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="M13 11h9M13 15h6"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                <span
                                    class="absolute -top-1 -right-1 h-3 w-3 rounded-full border-2 border-white bg-[#cfb187]"
                                ></span>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-700">
                                Your rewrite will appear here
                            </h3>
                            <p
                                class="mt-2 max-w-[230px] text-xs leading-5 text-slate-400"
                            >
                                Paste a reply or choose a sample, then let the
                                desk do a careful first pass.
                            </p>
                            <div
                                class="mt-6 flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-medium text-slate-400"
                            >
                                <span class="flex gap-0.5"
                                    ><i
                                        class="h-1 w-1 rounded-full bg-slate-300"
                                    ></i
                                    ><i
                                        class="h-1 w-1 rounded-full bg-slate-300"
                                    ></i
                                    ><i
                                        class="h-1 w-1 rounded-full bg-slate-300"
                                    ></i
                                ></span>
                                Your text stays in your workspace
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="space-y-4">
                    <section
                        class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-[0_6px_24px_rgba(25,38,52,0.035)]"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <p
                                    class="text-[11px] font-semibold tracking-[0.12em] text-slate-500 uppercase"
                                >
                                    Integrity check
                                </p>
                                <h2
                                    class="mt-1 text-sm font-semibold text-slate-900"
                                >
                                    Protected details
                                </h2>
                            </div>
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#f5f1e9] text-[#927650]"
                            >
                                <svg
                                    viewBox="0 0 18 18"
                                    fill="none"
                                    class="h-[17px] w-[17px]"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M9 2 14.5 4v4.2c0 3.5-2.3 6-5.5 8-3.2-2-5.5-4.5-5.5-8V4L9 2Z"
                                        stroke="currentColor"
                                        stroke-width="1.35"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="m6.7 8.5 1.6 1.6 3.2-3.3"
                                        stroke="currentColor"
                                        stroke-width="1.35"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>
                        </div>

                        <template v-if="result">
                            <div
                                v-if="result.kept_tokens.length"
                                class="space-y-2"
                            >
                                <div
                                    v-for="(token, index) in result.kept_tokens"
                                    :key="`${token}-${index}`"
                                    class="flex items-center justify-between gap-2 rounded-lg border px-2.5 py-2"
                                    :class="
                                        result.valid
                                            ? 'border-emerald-100 bg-emerald-50/50'
                                            : 'border-rose-100 bg-rose-50/50'
                                    "
                                >
                                    <span
                                        class="min-w-0 truncate font-mono text-xs font-semibold text-slate-700"
                                        >{{ token }}</span
                                    >
                                    <span
                                        class="flex shrink-0 items-center gap-1 text-[9px] font-semibold tracking-wide text-slate-500 uppercase"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="
                                                result.valid
                                                    ? 'bg-emerald-500'
                                                    : 'bg-rose-500'
                                            "
                                        ></span>
                                        {{ tokenLabel(token) }}
                                    </span>
                                </div>
                            </div>
                            <p
                                v-else
                                class="rounded-lg bg-slate-50 px-3 py-3 text-xs leading-5 text-slate-500"
                            >
                                No order IDs, emails, or numbers were detected
                                in this reply.
                            </p>
                        </template>
                        <div v-else class="space-y-2">
                            <div
                                v-for="item in [
                                    'Order IDs · HD-0000',
                                    'Email addresses',
                                    'Numbers · dates · quantities',
                                ]"
                                :key="item"
                                class="flex items-center gap-2.5 rounded-lg border border-slate-100 bg-slate-50/70 px-2.5 py-2.5 text-xs text-slate-500"
                            >
                                <span
                                    class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-300 text-[9px] text-slate-400"
                                    >✓</span
                                >
                                {{ item }}
                            </div>
                        </div>
                        <div class="mt-4 border-t border-slate-100 pt-3.5">
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span class="text-slate-500">Verification</span>
                                <span
                                    class="font-semibold"
                                    :class="
                                        result
                                            ? result.valid
                                                ? 'text-emerald-700'
                                                : 'text-rose-700'
                                            : 'text-slate-400'
                                    "
                                    >{{
                                        result
                                            ? result.valid
                                                ? 'Passed'
                                                : 'Failed'
                                            : 'Runs after rewrite'
                                    }}</span
                                >
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-2xl border border-[#e4ded3] bg-[#f4f0e8] p-5"
                    >
                        <div class="flex items-center gap-2 text-[#8e714c]">
                            <svg
                                viewBox="0 0 16 16"
                                fill="none"
                                class="h-4 w-4"
                                aria-hidden="true"
                            >
                                <path
                                    d="M8 2.3v11.4M2.3 8h11.4"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linecap="round"
                                />
                                <circle
                                    cx="8"
                                    cy="8"
                                    r="6.2"
                                    stroke="currentColor"
                                    stroke-width="1.2"
                                />
                            </svg>
                            <h2
                                class="text-[11px] font-semibold tracking-[0.12em] uppercase"
                            >
                                How it works
                            </h2>
                        </div>
                        <ol class="mt-4 space-y-3.5">
                            <li class="flex gap-3">
                                <span class="how-number">1</span>
                                <p class="text-xs leading-5 text-slate-600">
                                    Find IDs, emails, and whole numbers in the
                                    original.
                                </p>
                            </li>
                            <li class="flex gap-3">
                                <span class="how-number">2</span>
                                <p class="text-xs leading-5 text-slate-600">
                                    Ask the paraphraser for a warmer, clearer
                                    reply.
                                </p>
                            </li>
                            <li class="flex gap-3">
                                <span class="how-number">3</span>
                                <p class="text-xs leading-5 text-slate-600">
                                    Compare protected details before the result
                                    is cleared.
                                </p>
                            </li>
                        </ol>
                    </section>

                    <p class="px-1 text-[10px] leading-4 text-slate-400">
                        AI drafts can still be wrong. Review the wording and
                        facts before sending.
                    </p>
                </aside>
            </div>

            <footer
                class="mt-9 flex flex-col gap-2 border-t border-slate-200/80 pt-5 text-[11px] text-slate-400 sm:flex-row sm:items-center sm:justify-between"
            >
                <span
                    >Paraphrase Desk
                    <span class="mx-1 text-slate-300">/</span> A careful first
                    draft for support teams</span
                >
                <span
                    >Every rewrite is checked. Every reply gets a human
                    review.</span
                >
            </footer>
        </main>
    </div>
</template>
