# WeBLOG UI Redesign

## Goal

Replace the current interface with the selected light editorial style while preserving every existing feature and meeting the assignment requirement for a responsive, clean HTML, CSS, and JavaScript frontend.

## Visual System

- White and soft-gray surfaces
- Near-black text with a cobalt-blue accent
- Serif headings with readable sans-serif body text
- Flat borders, restrained shadows, and consistent spacing
- Compact navigation and hero sections
- Subtle CSS-only geometric decoration
- No frameworks, image assets, gradients, oversized headings, or excessive empty space

## Homepage

The newest blog appears as a featured story. Remaining blogs appear in a responsive card grid. Every blog remains accessible from the homepage. Cards use only existing information: title, excerpt, author, publication date, and comment count. Empty-state content replaces featured and grid sections when no blogs exist.

## Other Pages

The single-blog page uses a focused reading column with clear metadata, ownership actions, Markdown content, and comments. The editor uses a refined two-panel Markdown layout on desktop and a stacked layout on mobile. Login and registration use compact centered forms that share the same typography, controls, and colors.

## Delete Confirmation

Post and comment deletion use one in-page confirmation dialog instead of a browser-native prompt. Selecting Delete opens the dialog without submitting the form. The message identifies whether a post or comment will be deleted. Cancel, Escape, or selecting the backdrop closes the dialog without changing data. Confirming submits the original form to its existing PHP endpoint. The dialog uses the existing visual system, receives keyboard focus when opened, and restores focus to the original Delete button when cancelled.

The PHP deletion handlers, ownership rules, form fields, and redirect behavior remain unchanged. A browser regression verifies that both delete controls open the dialog and that confirmation submits the correct form.

## Responsive Behaviour

Desktop layouts use a centered maximum-width container. Blog cards adapt from three columns to two and then one. The editor stacks its writing and preview panels on narrow screens. Navigation, forms, buttons, article content, and comments must remain readable without horizontal overflow.

## Scope

Authentication, authorization, blog CRUD, Markdown rendering, hyperlinks, validation, character limits, and comments remain unchanged. Categories, reading-time calculations, subscriptions, bookmarks, uploads, and other new features are excluded.

## Verification

Verify PHP and JavaScript syntax, existing application flows, desktop and mobile layouts, keyboard-accessible controls, text contrast, empty states, and horizontal overflow. Remove temporary testing artifacts before creating a local-only commit.
