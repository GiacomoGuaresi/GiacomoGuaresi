from time import sleep
import time
from rich.align import Align
from rich.console import Console
from rich.progress import track
from rich.text import Text
from rich.panel import Panel
from rich import print
import os

console = Console()

# ASCII art del logo (può essere generata e incollata qui)
import shutil
from rich.console import Console
from rich.panel import Panel
from rich.text import Text

console = Console()

logo_ascii = """
 
             ##
            ####
           ##   #
         ##      ##
       ######      ##
     ##  # #         ##
   ##    # #           ##
 ##      # #             ##
 #  ###  # #              #
#  ##### # #               #
 # ###  ###               ##
 ##  #####               ##
   ####  ####  ####   ###
       #   #    #   #
          ##    ##
        ##        ##
        ############
 
"""
   
# Toolbox
toolbox = {
    "Operating Systems": ["Windows", "Linux"],
    "Languages": ["C++", "C#", "Java", "Python", "JavaScript", "TypeScript", "PHP"],
    "Frontend": ["React", "Angular", "jQuery", "Bootstrap"],
    "Backend": ["Node.js", "Spring", "Django", "Flask", ".NET"],
    "Databases": ["MySQL", "PostgreSQL", "MongoDB", "Redis", "Oracle"],
    "DevOps": ["Docker", "Kubernetes", "GitHub", "AWS", "Azure"]
}

# Frasi carine
phrases = [
    "Loading awesomeness...",
    "Injecting caffeine into code...",
    "Bringing ideas to life...",
    "Polishing semicolons...",
    "Unleashing developer power..."
]

def center_block(text):
    width = shutil.get_terminal_size().columns
    lines = text.strip('\n').splitlines()
    padding = max((width - max(len(line) for line in lines)) // 2, 0)
    return '\n'.join(' ' * padding + line for line in lines)

def install_tools(section, tools):
    console.rule(f"[bold cyan]{section}")
    for tool in track(tools, description=f"Installing {section}..."):
        sleep(0.3)
        console.print(f"[green] ✔ [/green] {tool} installed")

def intro():
    os.system('cls' if os.name == 'nt' else 'clear')
    centered_logo = center_block(logo_ascii)
    console.print(Panel(Text(centered_logo), title="[bold]Giacomo Guaresi", subtitle="Crafted with ♥ in code"))
    console.print("[bold yellow]Booting up Tech Toolbox Installer...[/bold yellow]\n")
    sleep(2)

def show_recap(duration: int = 20):
    """Mostra una schermata riepilogo dei tuoi skill per 'duration' secondi"""
    console.clear()
    recap = Text(justify="center")
    recap.append("🎯 Skill & Toolbox\n", style="bold magenta")
    recap.append("\n")
    for section, items in toolbox.items():
        recap.append(f"{section}:\n", style="bold cyan")
        recap.append(" • " + ", ".join(items) + "\n")
        recap.append("\n")
    
    panel = Panel(recap, title="Riepilogo", expand=False)
    aligned_panel = Align.center(panel, vertical="middle")
    console.print(aligned_panel)

    time.sleep(duration)
    console.clear()

def main():
    console.clear()
    time.sleep(1)

    intro()
    for phrase in phrases:
        console.print(f"[italic blue]{phrase}[/italic blue]")
        sleep(1)

    for section, tools in toolbox.items():
        install_tools(section, tools)
    
    console.print("\n[bold green]✅ Setup complete. Ready to deploy greatness![/bold green]")
    console.print("[dim]Tip: Always commit with love and coffee ☕[/dim]")
    
    time.sleep(5)
    
    show_recap(20)

if __name__ == "__main__":
    main()
