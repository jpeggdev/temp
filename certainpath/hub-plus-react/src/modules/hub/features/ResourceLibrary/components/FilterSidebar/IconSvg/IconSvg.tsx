import React from "react";

interface IconSvgProps {
  svg: string;
  className?: string;
}

export const IconSvg: React.FC<IconSvgProps> = ({ svg, className }) => {
  const processedSvg = svg.replace(/<svg([^>]*)>/, (match, attrs) => {
    const ensureAttr = (attr: string, value: string) =>
      new RegExp(`${attr}=`).test(attrs) ? "" : ` ${attr}="${value}"`;

    let newAttrs = attrs
      .replace(/stroke="[^"]*"/g, 'stroke="currentColor"')
      .replace(/fill="[^"]*"/g, 'fill="none"')
      .replace(/stroke-width="[^"]*"/g, 'stroke-width="2"')
      .replace(/stroke-linecap="[^"]*"/g, 'stroke-linecap="round"')
      .replace(/stroke-linejoin="[^"]*"/g, 'stroke-linejoin="round"');

    newAttrs += ensureAttr("stroke-width", "2");
    newAttrs += ensureAttr("stroke", "currentColor");
    newAttrs += ensureAttr("fill", "none");
    newAttrs += ensureAttr("stroke-linecap", "round");
    newAttrs += ensureAttr("stroke-linejoin", "round");

    return `<svg${newAttrs}>`;
  });

  return (
    <span
      className={className}
      dangerouslySetInnerHTML={{ __html: processedSvg }}
    />
  );
};
