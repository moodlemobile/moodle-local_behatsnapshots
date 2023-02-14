function escapeRegExp(text: string) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function replaceNodeText(node: Node, search: RegExp, replacement: string): void {
    for (const child of node.childNodes) {
        if (!(child instanceof Text)) {
            replaceNodeText(child, search, replacement);

            continue;
        }

        if (!child.textContent || !search.test(child.textContent)) {
            continue;
        }

        child.replaceWith(child.textContent?.replace(search, replacement));
    }
}

function replaceText(selector: string, text: string, replacement: string): void {
    const elements = document.querySelectorAll(selector);
    const search = (text.startsWith('/') && text.endsWith('/'))
        ? new RegExp(text.slice(1, -1))
        : new RegExp(escapeRegExp(text));

    for (const element of elements) {
        replaceNodeText(element, search, replacement);
    }
}

function setStyles(selector: string, property: string, value: string): void {
    const elements = document.querySelectorAll(selector);

    for (const element of elements) {
        if (!(element instanceof HTMLElement)) {
            continue;
        }

        element.style.setProperty(property, value);
    }
}

(window as any).localBehatSnapshots = { replaceText, setStyles };
