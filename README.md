# Hydrator

Configure objects hydration with attributes

Hydrate an existing object : 
Hydrator::build(classname, data)

Create and hydrate an object (must have HydratedObject attribute)
Hydrator::hydrate(object, data)

Only properties with HydratedField are processed, other are simply ignored

tests are the doc

les types unions et intersection ne sont pas gérés

le path spécial * permet d'accéder à toutes les données, éventuellement intéressant pour une factory
