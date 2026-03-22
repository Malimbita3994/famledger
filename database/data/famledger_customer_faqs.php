<?php

/**
 * Customer-facing FAQ entries for the marketing landing page and in-app FAQ
 * (notification_faqs table). Plain text; HTML is optional via Purify when edited in admin.
 *
 * @return list<array{group: string, question: string, answer: string}>
 */
return [
    [
        'group' => 'About FamLedger',
        'question' => 'What is FamLedger?',
        'answer' => 'FamLedger is family accounting software: one private ledger where you record income and expenses by wallet, reconcile balances, track properties and projects, and produce clear reports for family members and advisors—without juggling spreadsheets and chat threads.',
    ],
    [
        'group' => 'About FamLedger',
        'question' => 'Who is FamLedger for?',
        'answer' => 'Households and small family-style groups that want disciplined books—wallets, projects, rentals, or shared goals—in one place. It suits families who meet on the same numbers and optional accountants or advisors who review categories and reports with you.',
    ],
    [
        'group' => 'About FamLedger',
        'question' => 'Can several family members use the same ledger?',
        'answer' => 'Yes. You can invite members and use roles so the right people can view or manage wallets, transactions, and assets. Access is scoped to the family you are working in so everyone sees only what they are allowed to.',
    ],
    [
        'group' => 'About FamLedger',
        'question' => 'What is a wallet, and why use more than one?',
        'answer' => 'A wallet is a bucket for money movement—think a bank account, cash jar, or card you track separately. Multiple wallets let you split everyday spending, savings, or a rental project while still rolling up to one family view.',
    ],
    [
        'group' => 'About FamLedger',
        'question' => 'Can I track property, projects, or other assets?',
        'answer' => 'Yes. You can tie activity to projects and track assets such as property alongside ordinary income and expenses, so funding, spend, and ownership stay traceable in one system.',
    ],
    [
        'group' => 'About FamLedger',
        'question' => 'How do income and expenses work in FamLedger?',
        'answer' => 'You record money in and out with dates, categories, and the wallet they belong to. That builds balances, budgets, and reports your family (and advisors) can trust when you review the month or a specific project.',
    ],
    [
        'group' => 'About FamLedger',
        'question' => 'Can an accountant or advisor work from FamLedger?',
        'answer' => 'Yes. Consistent categories, balances, and exports or reports make reviews easier. You control what they can access according to your organisation’s roles and permissions.',
    ],
    [
        'group' => 'About FamLedger',
        'question' => 'How private is my data?',
        'answer' => 'Your ledger is tied to your account and family membership; only invited members with the right permissions can see your family’s data. Follow good password hygiene and keep your login details safe, as with any financial app.',
    ],
    [
        'group' => 'Plans & pricing',
        'question' => 'How much does FamLedger cost? Is there a trial?',
        'answer' => 'Pricing and trials may change—check the pricing section on this site or contact us from the contact area below. We are happy to point you to the current plan that fits your family.',
    ],
    [
        'group' => 'Notifications',
        'question' => 'How are notification emails batched?',
        'answer' => 'FamLedger groups lower-priority notifications into summary emails so your inbox stays readable, while critical alerts (for example failed payments or budget breaches) can be sent immediately according to your settings.',
    ],
    [
        'group' => 'Notifications',
        'question' => 'Can I disable all notifications temporarily?',
        'answer' => 'Yes. In Settings → Notifications, use the “Do not disturb” tab to pause delivery for a period without losing your saved channel and alert preferences.',
    ],
    [
        'group' => 'Notifications',
        'question' => 'Do notification settings apply per family or per account?',
        'answer' => 'Most delivery settings are per user account. Some alerts—such as thresholds tied to a specific family budget—apply in the context of the family you have active when the event occurs.',
    ],
    [
        'group' => 'Notifications',
        'question' => 'Will FamLedger change filters or rules in my email inbox?',
        'answer' => 'No. FamLedger only controls emails we send you. Any sorting rules, folders, or block lists in Gmail, Outlook, or other clients keep working exactly as you configured them.',
    ],
    [
        'group' => 'Notifications',
        'question' => 'Can owners require certain alerts for all members?',
        'answer' => 'Primary owners can require selected high-risk alerts (for example very large withdrawals) to stay enabled for members, so nothing critical is turned off by mistake.',
    ],
    [
        'group' => 'Notifications',
        'question' => 'Where can I see what notifications were sent?',
        'answer' => 'Notification history is being expanded over time. Check Settings for audit-related views where activity appears today, and expect richer notification history in future updates.',
    ],
    [
        'group' => 'Support',
        'question' => 'How do I get help or suggest a feature?',
        'answer' => 'Use the contact section on this page to reach us, or your in-app support links if your organisation provides them. We read product feedback and support requests regularly.',
    ],
];
