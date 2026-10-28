import { marked } from "marked";
import { filterXSS } from "xss";

// プレビューでもサーバー側の表示と同様に、on* 属性や javascript: URL など
// スクリプトを実行できる HTML を取り除く。許可しないタグは文字として表示せず、
// HTMLPurifier と同じくタグごと取り除き、script・style は中身も取り除く
export default function renderMarkdown(markdown) {
  return filterXSS(marked(markdown || ""), {
    stripIgnoreTag: true,
    stripIgnoreTagBody: ["script", "style"],
  });
}
