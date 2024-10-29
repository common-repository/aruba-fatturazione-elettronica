export function useTextFormatter(formatText) {

    const stringWithBoldOpenTags = formatText.replace(/\[b\]/g, '<b>');

    const finalFormattedString = stringWithBoldOpenTags.replace(/\[\/b\]/g, '</b>');

    return finalFormattedString;

}

export function parseFeSting(string) {
    return string.replace(/&#039;/g, "'");
}

export function parseAllLabels(obj) {

    for (let key in obj) {
        if (obj.hasOwnProperty(key) && typeof obj[key] === 'string') {

            obj[key] = obj[key].replace(/&#039;/g, "'");
        }
    }
    return obj;

}