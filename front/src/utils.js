export function getRgbValue(color) 
{
    const tempElement = document.createElement('div');
    tempElement.style.color = color;
    document.body.appendChild(tempElement);
    const rgbColor = getComputedStyle(tempElement).color;
    document.body.removeChild(tempElement);
    return rgbColor;
}