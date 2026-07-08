import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Link from '@tiptap/extension-link';
import { Toggle } from '@/components/ui/toggle';
import { Bold, Italic, Heading2, List, ListOrdered, Link as LinkIcon } from 'lucide-react';

type Props = {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    id?: string;
};

export function RichTextEditor({ value, onChange, placeholder, id }: Props) {
    const editor = useEditor({
        extensions: [
            StarterKit.configure({ heading: { levels: [2] } }),
            Placeholder.configure({ placeholder: placeholder ?? 'Start writing...' }),
            Link.configure({ openOnClick: false }),
        ],
        content: value,
        onUpdate: ({ editor }) => onChange(editor.getHTML()),
        editorProps: {
            attributes: {
                id,
                class: 'prose prose-sm max-w-none min-h-[200px] px-3 py-2 focus:outline-none',
            },
        },
        immediatelyRender: false,
    });

    if (!editor) return null;

    function toggleLink() {
        const previousUrl = editor.getAttributes('link').href;
        const url = window.prompt('Link URL', previousUrl ?? '');

        if (url === null) return;

        if (url === '') {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();
            return;
        }

        editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    }

    return (
        <div id={id} className="border-3 border-foreground bg-card focus-within:shadow-hard transition-shadow">
            <div className="flex flex-wrap gap-1 border-b-2 border-foreground bg-muted px-2 py-2">
                <Toggle size="sm" pressed={editor.isActive('bold')} onPressedChange={() => editor.chain().focus().toggleBold().run()} aria-label="Bold">
                    <Bold className="h-4 w-4" />
                </Toggle>
                <Toggle size="sm" pressed={editor.isActive('italic')} onPressedChange={() => editor.chain().focus().toggleItalic().run()} aria-label="Italic">
                    <Italic className="h-4 w-4" />
                </Toggle>
                <Toggle size="sm" pressed={editor.isActive('heading', { level: 2 })} onPressedChange={() => editor.chain().focus().toggleHeading({ level: 2 }).run()} aria-label="Heading">
                    <Heading2 className="h-4 w-4" />
                </Toggle>
                <Toggle size="sm" pressed={editor.isActive('bulletList')} onPressedChange={() => editor.chain().focus().toggleBulletList().run()} aria-label="Bullet list">
                    <List className="h-4 w-4" />
                </Toggle>
                <Toggle size="sm" pressed={editor.isActive('orderedList')} onPressedChange={() => editor.chain().focus().toggleOrderedList().run()} aria-label="Ordered list">
                    <ListOrdered className="h-4 w-4" />
                </Toggle>
                <Toggle size="sm" pressed={editor.isActive('link')} onPressedChange={toggleLink} aria-label="Link">
                    <LinkIcon className="h-4 w-4" />
                </Toggle>
            </div>
            <EditorContent editor={editor} />
        </div>
    );
}
